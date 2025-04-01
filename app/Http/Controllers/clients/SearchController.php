<?php

namespace App\Http\Controllers\clients;

use App\Http\Controllers\Controller;
use App\Models\clients\Tours;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SearchController extends Controller
{
    private $tours;

    public function __construct()
    {
        $this->tours = new Tours();
    }

    public function index(Request $request)
    {
        $title = 'Tìm kiếm';
        
        // Gọi API để lấy danh sách tỉnh thành
        try {
            $apiResponse = Http::get('https://provinces.open-api.vn/api/p/');
            $provinces = $apiResponse->successful() ? $apiResponse->json() : [];
        } catch (\Exception $e) {
            $provinces = [];
        }

        $destination = $request->input('destination');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Chuyển đổi định dạng ngày tháng
        $formattedStartDate = $startDate ? Carbon::createFromFormat('d/m/Y', $startDate)->format('Y-m-d') : null;
        $formattedEndDate = $endDate ? Carbon::createFromFormat('d/m/Y', $endDate)->format('Y-m-d') : null;

        // Lấy tên tỉnh thành từ API
        $destinationName = null;
        foreach ($provinces as $province) {
            if ($province['code'] == $destination) {
                $destinationName = $province['name'];
                break;
            }
        }

        $dataSearch = [
            'destination' => $destinationName,
            'startDate' => $formattedStartDate,
            'endDate' => $formattedEndDate,
        ];

        $tours = $this->tours->searchTours($dataSearch);

        return view('clients.search', compact('title', 'tours'));
    }

    public function searchTours(Request $request)
    {
        $title = 'Tìm kiếm';
        $keyword = $request->input('keyword');

        // Gọi API Python đã xử lý để lấy danh sách tour tìm kiếm
        try {
            $apiUrl = 'http://127.0.0.1:5555/api/search-tours';
            $response = Http::get($apiUrl, ['keyword' => $keyword]);
            $resultTours = $response->successful() ? $response->json('related_tours') : [];
        } catch (\Exception $e) {
            $resultTours = [];
        }

        if ($resultTours) {
            $tours = $this->tours->toursSearch($resultTours);
        } else {
            $dataSearch = ['keyword' => $keyword];
            $tours = $this->tours->searchTours($dataSearch);
        }

        return view('clients.search', compact('title', 'tours'));
    }
}
