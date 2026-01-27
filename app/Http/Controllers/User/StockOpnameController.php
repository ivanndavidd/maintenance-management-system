<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\StockOpnameSchedule;
use App\Models\StockOpnameScheduleItem;
use App\Services\StockOpnameService;
use App\Imports\StockOpnameImport;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StockOpnameController extends Controller
{
    protected $stockOpnameService;

    public function __construct(StockOpnameService $stockOpnameService)
    {
        $this->stockOpnameService = $stockOpnameService;
    }

    /**
     * Display list of stock opname schedules assigned to the user
     */
    public function index()
    {
        $user = auth()->user();

        // Get schedules where user is assigned
        $schedules = StockOpnameSchedule::whereHas('userAssignments', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['userAssignments.user'])
        ->where('status', 'active')
        ->orderBy('execution_date')
        ->paginate(15);

        return view('user.stock-opname.index', compact('schedules'));
    }

    /**
     * Show detail of a specific stock opname schedule
     */
    public function show(Request $request, StockOpnameSchedule $schedule)
    {
        $user = auth()->user();

        // Check if user is assigned to this schedule
        $isAssigned = $schedule->userAssignments()
            ->where('user_id', $user->id)
            ->exists();

        if (!$isAssigned) {
            return redirect()->route('user.stock-opname.index')
                ->with('error', 'You are not assigned to this schedule.');
        }

        // Load relationships
        $schedule->load(['createdByUser', 'userAssignments.user']);

        // Build query with filters
        $query = $schedule->scheduleItems();

        // Filter by item type
        if ($request->filled('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        // Filter by execution status
        if ($request->filled('status')) {
            $query->where('execution_status', $request->status);
        }

        // Paginate (50 per page) with filter persistence
        $scheduleItems = $query->paginate(50)->appends($request->except('page'));

        // Get statistics
        $stats = [
            'total_items' => $schedule->total_items,
            'completed_items' => $schedule->completed_items,
            'cancelled_items' => $schedule->cancelled_items,
            'pending_items' => $schedule->pendingItems()->count(),
            'progress_percentage' => $schedule->getProgressPercentage(),
            'days_remaining' => $schedule->getDaysRemaining(),
            'is_overdue' => $schedule->isOverdue(),
        ];

        return view('user.stock-opname.show', compact('schedule', 'scheduleItems', 'stats'));
    }

    /**
     * Execute single item (input physical quantity) - AJAX
     */
    public function executeItem(Request $request, StockOpnameScheduleItem $item)
    {
        $user = auth()->user();

        // Verify user is assigned to this schedule
        $schedule = $item->schedule;
        $isAssigned = $schedule->userAssignments()
            ->where('user_id', $user->id)
            ->exists();

        if (!$isAssigned) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this schedule.'
            ], 403);
        }

        // Validate input
        $request->validate([
            'physical_quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Use markCompleted method which handles discrepancy calculation and review status
        $item->markCompleted($user->id, $request->physical_quantity, $request->notes);

        return response()->json([
            'success' => true,
            'message' => 'Item executed successfully!',
            'data' => [
                'item_id' => $item->id,
                'execution_status' => $item->execution_status,
                'discrepancy_qty' => $item->discrepancy_qty,
                'review_status' => $item->review_status,
                'schedule_progress' => $schedule->getProgressPercentage(),
            ]
        ]);
    }

    /**
     * Get system quantity for item
     */
    private function getSystemQuantity(StockOpnameScheduleItem $item)
    {
        switch ($item->item_type) {
            case 'sparepart':
                $sparepart = \App\Models\Sparepart::find($item->item_id);
                return $sparepart ? $sparepart->quantity : 0;

            case 'tool':
                $tool = \App\Models\Tool::find($item->item_id);
                return $tool ? $tool->quantity : 0;

            case 'asset':
                // Assets typically have quantity = 1 (single unit tracking)
                $asset = \DB::table('assets_master')->where('id', $item->item_id)->first();
                return $asset ? 1 : 0;

            default:
                return 0;
        }
    }

    /**
     * Execute multiple items in batch - AJAX
     */
    public function executeBatch(Request $request)
    {
        $user = auth()->user();

        // Log incoming request for debugging
        \Log::info('executeBatch called', [
            'user_id' => $user->id,
            'request_data' => $request->all()
        ]);

        // Validate input
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:stock_opname_schedule_items,id',
            'items.*.physical_quantity' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($request->items as $itemData) {
            try {
                $item = StockOpnameScheduleItem::findOrFail($itemData['item_id']);
                $schedule = $item->schedule;

                \Log::info('Processing item', [
                    'item_id' => $item->id,
                    'schedule_id' => $schedule->id
                ]);

                // Verify user is assigned to this schedule
                $isAssigned = $schedule->userAssignments()
                    ->where('user_id', $user->id)
                    ->exists();

                \Log::info('User assignment check', [
                    'user_id' => $user->id,
                    'is_assigned' => $isAssigned
                ]);

                if (!$isAssigned) {
                    $errorCount++;
                    $errors[] = "Not assigned to item {$item->id}";
                    \Log::warning('User not assigned to schedule', [
                        'user_id' => $user->id,
                        'schedule_id' => $schedule->id,
                        'item_id' => $item->id
                    ]);
                    continue;
                }

                // Use markCompleted method which handles discrepancy calculation and review status
                $item->markCompleted($user->id, $itemData['physical_quantity'], $itemData['notes'] ?? null);

                $successCount++;
                \Log::info('Item saved successfully', ['item_id' => $item->id]);
            } catch (\Exception $e) {
                $errorCount++;
                $errorMsg = "Failed to save item {$itemData['item_id']}: {$e->getMessage()}";
                $errors[] = $errorMsg;
                \Log::error('Error saving item', [
                    'item_id' => $itemData['item_id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        if ($errorCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Saved {$successCount} items, failed {$errorCount} items",
                'errors' => $errors,
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully saved {$successCount} items",
            'data' => [
                'count' => $successCount,
            ]
        ]);
    }

    /**
     * Cancel item execution (mark as cancelled) - AJAX
     */
    public function cancelItem(Request $request, StockOpnameScheduleItem $item)
    {
        $user = auth()->user();

        // Verify user is assigned to this schedule
        $schedule = $item->schedule;
        $isAssigned = $schedule->userAssignments()
            ->where('user_id', $user->id)
            ->exists();

        if (!$isAssigned) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this schedule.'
            ], 403);
        }

        // Validate input
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        // Update item
        $item->execution_status = 'cancelled';
        $item->notes = $request->cancellation_reason;
        $item->executed_by = $user->id;
        $item->executed_at = now();
        $item->save();

        // Update schedule progress
        $schedule->updateProgress();

        return response()->json([
            'success' => true,
            'message' => 'Item cancelled successfully!',
            'data' => [
                'item_id' => $item->id,
                'execution_status' => $item->execution_status,
                'schedule_progress' => $schedule->getProgressPercentage(),
            ]
        ]);
    }

    /**
     * Export Excel template for stock opname
     */
    public function exportTemplate(StockOpnameSchedule $schedule)
    {
        $user = auth()->user();

        // Check if user is assigned to this schedule
        $isAssigned = $schedule->userAssignments()
            ->where('user_id', $user->id)
            ->exists();

        if (!$isAssigned) {
            return redirect()->route('user.stock-opname.index')
                ->with('error', 'You are not assigned to this schedule.');
        }

        $filename = 'StockOpname_' . $schedule->schedule_code . '_' . date('Ymd_His') . '.xlsx';

        // Get pending items
        $items = $schedule->scheduleItems()
            ->with(['sparepart', 'tool', 'asset'])
            ->where('execution_status', 'pending')
            ->get();

        return response()->streamDownload(function() use ($items) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set sheet title
            $sheet->setTitle('Stock Opname');

            // Define headers (tanpa Item ID dan System Qty untuk meningkatkan akurasi)
            $headers = ['Item Type', 'Item Code', 'Item Name', 'Location', 'Physical Qty', 'Notes'];

            // Set headers in row 1
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }

            // Style header row
            $sheet->getStyle('A1:F1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Fill data rows
            $row = 2;
            foreach ($items as $item) {
                $location = '-';
                if ($item->item_type === 'sparepart' && $item->sparepart) {
                    $location = $item->sparepart->location ?? '-';
                } elseif ($item->item_type === 'asset' && $item->asset) {
                    $location = $item->asset->location ?? '-';
                }

                $sheet->setCellValue('A' . $row, ucfirst($item->item_type));
                // Set Item Code as text to prevent scientific notation
                $sheet->setCellValueExplicit('B' . $row, $item->getItemCode(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('C' . $row, $item->getItemName());
                $sheet->setCellValue('D' . $row, $location);
                $sheet->setCellValue('E' . $row, ''); // Physical Qty - to be filled
                $sheet->setCellValue('F' . $row, ''); // Notes - optional

                $row++;
            }

            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(12);  // Item Type
            $sheet->getColumnDimension('B')->setWidth(20);  // Item Code
            $sheet->getColumnDimension('C')->setWidth(40);  // Item Name
            $sheet->getColumnDimension('D')->setWidth(15);  // Location
            $sheet->getColumnDimension('E')->setWidth(15);  // Physical Qty
            $sheet->getColumnDimension('F')->setWidth(30);  // Notes

            // Add borders to all cells with data
            if ($row > 2) {
                $sheet->getStyle('A1:F' . ($row - 1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename);
    }

    /**
     * Import Excel file with stock opname results (returns JSON for UI preview)
     */
    public function importExcel(Request $request, StockOpnameSchedule $schedule)
    {
        $user = auth()->user();

        // Check if user is assigned to this schedule
        $isAssigned = $schedule->userAssignments()
            ->where('user_id', $user->id)
            ->exists();

        if (!$isAssigned) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this schedule.'
            ], 403);
        }

        // Validate file
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('excel_file');
            $import = new StockOpnameImport($user->id, $schedule->id);

            // Call import method with file path (only parses, doesn't save)
            $import->import($file->getRealPath());

            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();
            $importedData = $import->getImportedData();

            return response()->json([
                'success' => true,
                'message' => "Berhasil membaca {$successCount} item dari Excel.",
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => array_slice($errors, 0, 10),
                'data' => $importedData, // Data to fill in UI
            ]);

        } catch (\Exception $e) {
            \Log::error('Stock Opname Import Error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'schedule_id' => $schedule->id,
                'file' => $request->file('excel_file')->getClientOriginalName(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
