<?php

namespace App\Http\Controllers\clients;

use Illuminate\Http\Request;
use App\Models\clients\Tours;
use App\Models\clients\ChatbotResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http; // Dùng để gọi API

class ChatbotController extends Controller
{
    public function getResponse(Request $request)
    {
        $message = strtolower($request->input('message')); // Get the user's message
        $response = '';

        if (str_contains($message, 'gợi ý') || str_contains($message, 'bất kỳ')){
            $response = $this->suggestRandomTour();
        } elseif (str_contains($message, 'thời tiết')) {
            $response = $this->getWeatherInfo($message);
        } elseif (str_contains($message, 'tour') || str_contains($message, 'tours')) {
            $response = $this->listAllTours();
        } elseif (str_contains($message, 'đổi ngôn ngữ sang')) {
            $response = $this->changeLanguage($message);
        } else {
            // Kiểm tra phản hồi được định nghĩa trước trong cơ sở dữ liệu
            $chatbotResponse = ChatbotResponse::where('keyword', 'LIKE', "%{$message}%")->first();
        
            if ($chatbotResponse) {
                $response = $chatbotResponse->response;
            } else {
                $response = __("Xin lỗi, tôi không hiểu. Bạn có thể diễn đạt lại được không?");
            }
        }

        return response()->json(['response' => $response]);
    }

    // Hàm hiển thị tất cả các tour
    private function listAllTours()
    {
        $tours = Tours::select('tourId', 'title', 'destination', 'priceAdult')->get();

        if ($tours->isEmpty()) {
            return __('Rất tiếc, hiện tại chúng tôi không có tour nào.');
        }

        $response = __("Sau đây là một số tour du lịch hiện có của chúng tôi:") . "<br>";
        foreach ($tours as $tour) {
            $response .= "<div style='margin-bottom: 10px;'>
                            <strong>{$tour->title}</strong><br>
                            " . __("Điểm đến") . ": {$tour->destination}<br>
                            " . __("Giá") . ": {$tour->priceAdult} VND<br>
                            <a href='/tour-detail/{$tour->tourId}' class='btn btn-primary'>" . __("Xem chi tiết") . "</a>
                        </div>";
        }

        return $response;
    }

    // Hàm gợi ý một tour ngẫu nhiên
    private function suggestRandomTour()
    {
        $tour = Tours::inRandomOrder()->first();

        if (!$tour) {
            return __('Rất tiếc, hiện tại chúng tôi không có tour nào.');
        }

        return __("Chúng tôi gợi ý bạn tour:") . "<br>
                <strong>{$tour->title}</strong><br>
                " . __("Điểm đến") . ": {$tour->destination}<br>
                " . __("Giá") . ": {$tour->priceAdult} VND<br>
                <a href='/tour-detail/{$tour->tourId}' class='btn btn-primary'>" . __("Xem chi tiết") . "</a>";
    }

    // Hàm lấy thông tin thời tiết
    private function getWeatherInfo($message)
    {
        // Giả sử người dùng nhập "thời tiết tại Hà Nội"
        preg_match('/thời tiết tại (.+)/', $message, $matches);
        $location = $matches[1] ?? 'Hà Nội';

        $apiKey = '0cab6ec6144bf6bf37f30036e368227b'; // Thay bằng API key của bạn
        $url = "https://api.openweathermap.org/data/2.5/weather?q={$location}&appid={$apiKey}&units=metric&lang=vi";

        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            return __("Thời tiết tại") . " {$location}: " . $data['weather'][0]['description'] . ", " . 
                   __("nhiệt độ") . ": " . $data['main']['temp'] . "°C.";
        }

        return __("Xin lỗi, tôi không thể lấy thông tin thời tiết tại địa điểm này.");
    }
    
}