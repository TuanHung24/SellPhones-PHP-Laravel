<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\ProductDetail;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatisticalController extends Controller
{
    public function getListMonth()
    {
        $inVoice = Invoice::whereBetween('status', [1, 5])->count();
        $backInvoice = Invoice::where('status', 5)->count();

        $toTal = Invoice::where('status', 4)->sum('total');
        $totalInvoice = number_format($toTal, 0, ',', '.');

        $cusTomer = Customer::count();
        $quantityProduct = ProductDetail::sum('quantity');

        $priceWarehouse = Warehouse::sum('total');
        $totalWarehouse = number_format($priceWarehouse, 0, ',', '.');

        $sellProduct = InvoiceDetail::select('product_id')
            ->selectRaw('SUM(quantity) as totalpd')
            ->groupBy('product_id')
            ->orderByDesc('totalpd')
            ->take(3)
            ->get();

        return view('statistical-month', compact('inVoice', 'backInvoice', 'totalInvoice', 'sellProduct', 'cusTomer', 'quantityProduct', 'totalWarehouse'));
    }


    public function getListYear()
    {
        $inVoice = Invoice::whereBetween('status', [1, 5])->count();
        $backInvoice = Invoice::where('status', 5)->count();

        $toTal = Invoice::where('status', 4)->sum('total');
        $totalInvoice = number_format($toTal, 0, ',', '.');

        $cusTomer = Customer::count();
        $quantityProduct = ProductDetail::sum('quantity');

        $priceWarehouse = Warehouse::sum('total');
        $totalWarehouse = number_format($priceWarehouse, 0, ',', '.');

        $sellProduct = InvoiceDetail::select('product_id')
            ->selectRaw('SUM(quantity) as totalpd')
            ->groupBy('product_id')
            ->orderByDesc('totalpd')
            ->take(3)
            ->get();

        return view('statistical', compact('inVoice', 'backInvoice', 'totalInvoice', 'sellProduct', 'cusTomer', 'quantityProduct', 'totalWarehouse'));
    }

    public function statisticalMonth(Request $request)
    {
        try {
            $Month = $request->month;
            $Year = $request->year;

            if ($Month && $Year) {
                $data = Invoice::join('invoice_detail', 'invoice.id', '=', 'invoice_detail.invoice_id')
                    ->whereYear('invoice.created_at', $Year)
                    ->whereMonth('invoice.created_at', $Month)
                    ->whereBetween('invoice.status', [1, 4])
                    ->select(
                        DB::raw('DATE(invoice.created_at) as date'),
                        DB::raw('COUNT(DISTINCT invoice.id) as count'),
                        DB::raw('SUM(invoice_detail.into_money) as tongtien'),
                        DB::raw('SUM(invoice_detail.quantity) as soluong')
                    )
                    ->groupBy(DB::raw('DATE(invoice.created_at)'))
                    ->get();
            } else {
                $data = [];
            }

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Lỗi máy chủ'], 500);
        }
    }


    public function statisticalYear1(Request $request)
    {
        // try {
        $Year = $request->year;




        $inVoice = Invoice::whereBetween('status', [1, 5])->whereYear('date',$Year)->count();
        $backInvoice = Invoice::where('status', 5)->count();

        $toTal = Invoice::whereYear('date',$Year)->where('status', 4)->sum('total');
        $totalInvoice = number_format($toTal, 0, ',', '.');

        $cusTomer = Customer::whereYear('created_at',$Year)->count();
        //$quantityProduct = ProductDetail::sum('quantity');

        $priceWarehouse = Warehouse::whereYear('date',$Year)->sum('total');
        $totalWarehouse = number_format($priceWarehouse, 0, ',', '.');

        $sellProduct = InvoiceDetail::select('products.name as product_name', 'colors.name as color_name', 'capacity.name as capacity_name')
            ->selectRaw('SUM(quantity) as totalpd')
            ->join('products', 'invoice_detail.product_id', '=', 'products.id')
            ->join('colors', 'invoice_detail.color_id', '=', 'colors.id')
            ->join('capacity', 'invoice_detail.capacity_id', '=', 'capacity.id')
            ->whereHas('invoice', function ($query) use ($Year) {
                $query->where('status', 4); // Điều kiện trạng thái hóa đơn là 4
                if ($Year) {
                    $query->whereYear('date', $Year);
                }
            })
            ->groupBy('products.name', 'colors.name', 'capacity.name')
            ->orderByDesc('totalpd')
            ->take(3)
            ->get();


        if ($Year) {
            $invoice = Invoice::join('invoice_detail', 'invoice.id', '=', 'invoice_detail.invoice_id')
                ->whereYear('invoice.date', $Year)
                ->whereBetween('invoice.status', [1, 4])
                ->select(
                    DB::raw('MONTH(invoice.date) as month'),
                    DB::raw('COUNT(DISTINCT invoice.id) as count')
                )
                ->groupBy(DB::raw('MONTH(invoice.date)'))
                ->get();
        } else {
            $invoice = [];
        }
        $data=[
            'inVoice'=>$inVoice,
            'backInvoice'=> $backInvoice,
            'totalInvoice'=>$totalInvoice,
            'cusTomer'=>$cusTomer,
            'totalWarehouse'=>$totalWarehouse,
            'sellProduct'=>$sellProduct,
            'invoice'=>$invoice
        ];
        return response()->json($data);
        // } catch (\Exception $e) {
        //     Log::error($e->getMessage());
        //     return response()->json(['error' => 'Lỗi máy chủ'], 500);
        // }
    }


    public function hdstatisticalYear(Request $request)
    {
        $year = $request->year;

        $sellProduct = InvoiceDetail::select('products.name as product_name', 'colors.name as color_name', 'capacity.name as capacity_name')
            ->selectRaw('SUM(quantity) as totalpd')
            ->join('products', 'invoice_detail.product_id', '=', 'products.id')
            ->join('colors', 'invoice_detail.color_id', '=', 'colors.id')
            ->join('capacity', 'invoice_detail.capacity_id', '=', 'capacity.id')
            ->whereHas('invoice', function ($query) use ($year) {
                $query->where('status', 4); // Điều kiện trạng thái hóa đơn là 4
                if ($year) {
                    $query->whereYear('date', $year);
                }
            })
            ->groupBy('products.name', 'colors.name', 'capacity.name')
            ->orderByDesc('totalpd')
            ->take(3)
            ->get();

        function applyYearFilters($query, $year)
        {
            if ($year) {
                $query->whereYear('date', $year);
            }
            return $query;
        }

        $statuses = [
            'cho_xu_ly' => applyYearFilters(Invoice::where('status', Invoice::TRANG_THAI_CHO_XU_LY), $year)->count(),
            'da_duyet' => applyYearFilters(Invoice::where('status', Invoice::TRANG_THAI_DA_DUYET), $year)->count(),
            'dang_giao' => applyYearFilters(Invoice::where('status', Invoice::TRANG_THAI_DANG_GIAO), $year)->count(),
            'da_giao' => applyYearFilters(Invoice::where('status', Invoice::TRANG_THAI_HOAN_THANH), $year)->count(),
            'da_huy' => applyYearFilters(Invoice::where('status', Invoice::TRANG_THAI_DA_HUY), $year)->count(),
        ];

        $data = [
            'sellProduct' => $sellProduct,
            'statuses' => $statuses,
        ];

        return response()->json($data);
    }

    public function statisticalDay()
    {



        return view('statistical-day');
    }

    public function hdstatisticalDay(Request $request)
    {
        $day = $request->day;
        $month = $request->month;
        $year = $request->year;


        $revenue = Invoice::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereDay('date', $day)
            ->where('status', 4)
            ->sum('total');


        $sellProduct = InvoiceDetail::select('products.name as product_name', 'colors.name as color_name', 'capacity.name as capacity_name')
            ->selectRaw('SUM(quantity) as totalpd')
            ->join('products', 'invoice_detail.product_id', '=', 'products.id')
            ->join('colors', 'invoice_detail.color_id', '=', 'colors.id')
            ->join('capacity', 'invoice_detail.capacity_id', '=', 'capacity.id')
            ->whereHas('invoice', function ($query) use ($day, $month, $year) {
                $query->where('status', 4);
                if ($day) {
                    $query->whereDay('date', $day);
                }
                if ($month) {
                    $query->whereMonth('date', $month);
                }
                if ($year) {
                    $query->whereYear('date', $year);
                }
            })
            ->groupBy('products.name', 'colors.name', 'capacity.name')
            ->orderByDesc('totalpd')
            ->take(3)
            ->get();



        function applyDayFilters($query, $day, $month, $year)
        {
            if ($day) {
                $query->whereDay('date', $day);
            }
            if ($month) {
                $query->whereMonth('date', $month);
            }
            if ($year) {
                $query->whereYear('date', $year);
            }
            return $query;
        }

        $statuses = [
            'cho_xu_ly' => applyDayFilters(Invoice::where('status', Invoice::TRANG_THAI_CHO_XU_LY), $day, $month, $year)->count(),
            'da_duyet' => applyDayFilters(Invoice::where('status', Invoice::TRANG_THAI_DA_DUYET), $day, $month, $year)->count(),
            'dang_giao' => applyDayFilters(Invoice::where('status', Invoice::TRANG_THAI_DANG_GIAO), $day, $month, $year)->count(),
            'da_giao' => applyDayFilters(Invoice::where('status', Invoice::TRANG_THAI_HOAN_THANH), $day, $month, $year)->count(),
            'da_huy' => applyDayFilters(Invoice::where('status', Invoice::TRANG_THAI_DA_HUY), $day, $month, $year)->count(),
        ];


        $data = [
            'sellProduct' => $sellProduct,
            'statuses' => $statuses,
            'revenue' => $revenue,
        ];

        return response()->json($data);
    }


    public function hdstatisticalMonth(Request $request)
    {
        $month = $request->month;
        $year = $request->year;

        $sellProduct = InvoiceDetail::select('products.name as product_name', 'colors.name as color_name', 'capacity.name as capacity_name')
            ->selectRaw('SUM(quantity) as totalpd')
            ->join('products', 'invoice_detail.product_id', '=', 'products.id')
            ->join('colors', 'invoice_detail.color_id', '=', 'colors.id')
            ->join('capacity', 'invoice_detail.capacity_id', '=', 'capacity.id')
            ->whereHas('invoice', function ($query) use ($month, $year) {
                $query->where('status', 4); // Điều kiện trạng thái hóa đơn là 4
                if ($month) {
                    $query->whereMonth('date', $month);
                }
                if ($year) {
                    $query->whereYear('date', $year);
                }
            })
            ->groupBy('products.name', 'colors.name', 'capacity.name')
            ->orderByDesc('totalpd')
            ->take(3)
            ->get();


        function applyMonthFilters($query, $month, $year)
        {
            if ($month) {
                $query->whereMonth('date', $month);
            }
            if ($year) {
                $query->whereYear('date', $year);
            }
            return $query;
        }

        $statuses = [
            'cho_xu_ly' => applyMonthFilters(Invoice::where('status', Invoice::TRANG_THAI_CHO_XU_LY), $month, $year)->count(),
            'da_duyet' => applyMonthFilters(Invoice::where('status', Invoice::TRANG_THAI_DA_DUYET), $month, $year)->count(),
            'dang_giao' => applyMonthFilters(Invoice::where('status', Invoice::TRANG_THAI_DANG_GIAO), $month, $year)->count(),
            'da_giao' => applyMonthFilters(Invoice::where('status', Invoice::TRANG_THAI_HOAN_THANH), $month, $year)->count(),
            'da_huy' => applyMonthFilters(Invoice::where('status', Invoice::TRANG_THAI_DA_HUY), $month, $year)->count(),
        ];

        $data = [
            'sellProduct' => $sellProduct,
            'statuses' => $statuses,
        ];

        return response()->json($data);
    }
    public function statisticalYear(Request $request)
    {
        try {

            $Year = $request->year;

            if ($Year) {
                $data = Invoice::join('invoice_detail', 'invoice.id', '=', 'invoice_detail.invoice_id')
                    ->whereYear('invoice.created_at', $Year)

                    ->whereBetween('invoice.status', [1, 4])
                    ->select(
                        DB::raw('DATE(invoice.created_at) as date'),
                        DB::raw('COUNT(DISTINCT invoice.id) as count'),
                        DB::raw('SUM(invoice_detail.into_money) as tongtien'),
                        DB::raw('SUM(invoice_detail.quantity) as soluong')
                    )
                    ->groupBy(DB::raw('DATE(invoice.created_at)'))
                    ->get();
            } else {
                $data = [];
            }

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Lỗi máy chủ'], 500);
        }
    }
}
