<?php
namespace Database\Seeders;
use App\Models\Destination;
use Illuminate\Database\Seeder;

class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        $destinations = [
            ['name' => 'Diani Beach', 'location' => 'Kwale County', 'from_location' => 'Mombasa', 'to_location' => 'Ukunda Airstrip (UKA) - Diani', 'description' => 'Crystal clear waters and white sandy beaches on Kenyas stunning south coast.', 'image_url' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600&q=80', 'tag' => 'Beach'],
            ['name' => 'Mount Kenya', 'location' => 'Central Kenya', 'from_location' => 'Nairobi', 'to_location' => 'Nanyuki', 'description' => 'Africas second highest mountain, perfect for trekking and adventure seekers.', 'image_url' => 'https://images.unsplash.com/photo-1611735341450-74d61e660ad2?w=600&q=80', 'tag' => 'Adventure'],
            ['name' => 'Lake Nakuru', 'location' => 'Nakuru County', 'from_location' => 'Nairobi', 'to_location' => 'Nakuru', 'description' => 'Famous for flamingos and rhino sanctuary in the Great Rift Valley.', 'image_url' => 'https://images.unsplash.com/photo-1547970810-dc1eac37d174?w=600&q=80', 'tag' => 'Wildlife'],
            ['name' => 'Amboseli', 'location' => 'Kajiado County', 'from_location' => 'Nairobi', 'to_location' => 'Narok', 'description' => 'Elephants against the backdrop of Mount Kilimanjaro.', 'image_url' => 'https://images.unsplash.com/photo-1549366021-9f761d450615?w=600&q=80', 'tag' => 'Wildlife'],
            ['name' => 'Lamu Island', 'location' => 'Lamu County', 'from_location' => 'Nairobi', 'to_location' => 'Malindi', 'description' => 'A UNESCO World Heritage Swahili town rich in history and culture.', 'image_url' => 'https://images.unsplash.com/photo-1518998053901-5348d3961a04?w=600&q=80', 'tag' => 'Culture'],
            ['name' => 'Tsavo', 'location' => 'Coast & Eastern', 'from_location' => 'Nairobi', 'to_location' => 'Voi', 'description' => 'One of the largest game reserves, known for its red elephants.', 'image_url' => 'https://images.unsplash.com/photo-1534177616072-ef7dc120449d?w=600&q=80', 'tag' => 'Wildlife'],
            ['name' => 'Kisumu', 'location' => 'Nyanza', 'from_location' => 'Nairobi', 'to_location' => 'Kisumu', 'description' => 'Lake Victoria is Africas largest lake by surface area and the worlds largest tropical lake. Spanning approximately 68,800 square kilometres, it ranks as the worlds second-largest freshwater lake.', 'image_url' => 'https://journeysbydesign.com/wp-content/uploads/2016/12/Lake-Victoria-Dhow.jpg', 'tag' => 'Nature'],
            ['name' => 'Maasai Mara', 'location' => 'Narok County', 'from_location' => 'Nairobi', 'to_location' => 'Narok', 'description' => 'The Maasai Mara National Reserve is Kenyas premier wildlife destination, globally renowned for its big cats, sweeping savannah landscapes, and the annual Great Wildebeest Migration. Located in south-western Kenya along the Great Rift Valley, it forms a continuous, unfenced ecosystem with Tanzanias Serengeti National Park.', 'image_url' => 'https://www.tsavonationalparkkenya.com/wp-content/uploads/2023/12/maasai-mara-1110x700-4-750x450.jpg', 'tag' => 'Wildlife'],
        ];
        foreach ($destinations as $d) {
            Destination::updateOrCreate(['name' => $d['name']], $d);
        }
    }
}
