<?php

namespace App\Http\Controllers;

use App\Models\CompanyModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    public function saveLocation(Request $request)
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $address = $request->input('address');

        if (!$latitude || !$longitude) {
            if ($address) {
                $coordinates = $this->getCoordinatesFromAddress($address);
                $latitude = $coordinates['lat'];
                $longitude = $coordinates['lng'];
            } else {
                return response()->json(['error' => 'Location or address required'], 400);
            }
        }

        // Salve a localização no banco de dados
        $location = new CompanyModel();
        $location->latitude = $latitude;
        $location->longitude = $longitude;
        $location->save();

        // Gere links para visualização nos serviços de mapas
        $googleMapsUrl = "https://www.google.com/maps/search/?api=1&query={$latitude},{$longitude}";
        $appleMapsUrl = "http://maps.apple.com/?q={$latitude},{$longitude}";
        $wazeUrl = "https://waze.com/ul?ll={$latitude},{$longitude}&navigate=yes";

        return response()->json([
            'success' => 'Location saved successfully',
            'google_maps_url' => $googleMapsUrl,
            'apple_maps_url' => $appleMapsUrl,
            'waze_url' => $wazeUrl,
        ]);
    }

    private function getCoordinatesFromAddress($address)
    {
        $apiKey = config('services.google_maps.api_key');
        $response = Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
            'address' => $address,
            'key' => $apiKey,
        ]);

        $data = $response->json();
        if ($data['status'] == 'OK') {
            return $data['results'][0]['geometry']['location'];
        } else {
            throw new \Exception('Unable to get coordinates');
        }
    }
}
