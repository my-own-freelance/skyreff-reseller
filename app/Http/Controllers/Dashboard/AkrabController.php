<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class AkrabController extends Controller
{
    public function index()
    {
        $title = "Data Akrab";
        return view("pages.dashboard.admin.akrab", compact('title'));
    }

    public function getStockData()
    {
        try {
            $client = new Client();
            // Request ke API eksternal
            $response = $client->get('https://cek-stock.vercel.app/api/cek-stok');

            // Ambil body response & decode ke array
            $apiResponse = json_decode($response->getBody()->getContents(), true);

            // Cek apakah response dari API valid
            if (!$apiResponse || !isset($apiResponse['data'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid response from API',
                    'response' => $apiResponse
                ], 500);
            }

            $processedData = [];
            foreach ($apiResponse['data'] as $key => $value) {
                // Ubah nama key menjadi format nama produk yang lebih rapi
                $formattedName = ucwords(str_replace('_', ' ', $key));

                // Tambahkan ke array jika stok lebih dari 0
                $processedData[] = [
                    'name' => $formattedName,
                    'stock' => $value
                ];
            }


            return response()->json([
                'success' => true,
                'data' => $processedData
            ], 200);
        } catch (\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage()
            ], 500);
        }
    }
}
