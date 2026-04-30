<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ItemClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classifications = [
            ['code' => '001', 'description' => 'Breastfeeding equipment', 'sort_order' => 1],
            ['code' => '002', 'description' => 'Child care centres and kindergartens fees', 'sort_order' => 2],
            ['code' => '003', 'description' => 'Computer, smartphone or tablet', 'sort_order' => 3],
            ['code' => '004', 'description' => 'Consolidated e-Invoice', 'sort_order' => 4],
            ['code' => '005', 'description' => 'Construction materials (as specified under Fourth Schedule of the Lembaga Pembangunan Industri Pembinaan Malaysia Act 1994)', 'sort_order' => 5],
            ['code' => '006', 'description' => 'Disbursement', 'sort_order' => 6],
            ['code' => '007', 'description' => 'Donation', 'sort_order' => 7],
            ['code' => '008', 'description' => 'e-Commerce - e-Invoice to buyer / purchaser', 'sort_order' => 8],
            ['code' => '009', 'description' => 'e-Commerce - Self-billed e-Invoice to seller, logistics, etc.', 'sort_order' => 9],
            ['code' => '010', 'description' => 'Education fees', 'sort_order' => 10],
            ['code' => '011', 'description' => 'Goods on consignment (Consignor)', 'sort_order' => 11],
            ['code' => '012', 'description' => 'Goods on consignment (Consignee)', 'sort_order' => 12],
            ['code' => '013', 'description' => 'Gym membership', 'sort_order' => 13],
            ['code' => '014', 'description' => 'Insurance - Education and medical benefits', 'sort_order' => 14],
            ['code' => '015', 'description' => 'Insurance - Takaful or life insurance', 'sort_order' => 15],
            ['code' => '016', 'description' => 'Interest and financing expenses', 'sort_order' => 16],
            ['code' => '017', 'description' => 'Internet subscription', 'sort_order' => 17],
            ['code' => '018', 'description' => 'Land and building', 'sort_order' => 18],
            ['code' => '019', 'description' => 'Medical examination for learning disabilities and early intervention or rehabilitation treatments of learning disabilities', 'sort_order' => 19],
            ['code' => '020', 'description' => 'Medical examination or vaccination expenses', 'sort_order' => 20],
            ['code' => '021', 'description' => 'Medical expenses for serious diseases', 'sort_order' => 21],
            ['code' => '022', 'description' => 'Others', 'sort_order' => 22],
            ['code' => '023', 'description' => 'Petroleum operations (as defined in Petroleum (Income Tax) Act 1967)', 'sort_order' => 23],
            ['code' => '024', 'description' => 'Private retirement scheme or deferred annuity scheme', 'sort_order' => 24],
            ['code' => '025', 'description' => 'Motor vehicle', 'sort_order' => 25],
            ['code' => '026', 'description' => 'Subscription of books / journals / magazines / newspapers / other similar publications', 'sort_order' => 26],
            ['code' => '027', 'description' => 'Reimbursement', 'sort_order' => 27],
            ['code' => '028', 'description' => 'Rental of motor vehicle', 'sort_order' => 28],
            ['code' => '029', 'description' => 'EV charging facilities (Installation, rental, sale / purchase or subscription fees)', 'sort_order' => 29],
            ['code' => '030', 'description' => 'Repair and maintenance', 'sort_order' => 30],
            ['code' => '031', 'description' => 'Research and development', 'sort_order' => 31],
            ['code' => '032', 'description' => 'Foreign income', 'sort_order' => 32],
            ['code' => '033', 'description' => 'Self-billed - Betting and gaming', 'sort_order' => 33],
            ['code' => '034', 'description' => 'Self-billed - Importation of goods', 'sort_order' => 34],
            ['code' => '035', 'description' => 'Self-billed - Importation of services', 'sort_order' => 35],
            ['code' => '036', 'description' => 'Self-billed - Others', 'sort_order' => 36],
            ['code' => '037', 'description' => 'Self-billed - Monetary payment to agents, dealers or distributors', 'sort_order' => 37],
            ['code' => '038', 'description' => 'Sports equipment, rental / entry fees for sports facilities, registration in sports competition or sports training fees imposed by associations / sports clubs / companies registered with the Sports Commissioner or Companies Commission of Malaysia and carrying out sports activities as listed under the Sports Development Act 1997', 'sort_order' => 38],
            ['code' => '039', 'description' => 'Supporting equipment for disabled person', 'sort_order' => 39],
            ['code' => '040', 'description' => 'Voluntary contribution to approved provident fund', 'sort_order' => 40],
            ['code' => '041', 'description' => 'Dental examination or treatment', 'sort_order' => 41],
            ['code' => '042', 'description' => 'Fertility treatment', 'sort_order' => 42],
            ['code' => '043', 'description' => 'Treatment and home care nursing, daycare centres and residential care centers', 'sort_order' => 43],
            ['code' => '044', 'description' => 'Vouchers, gift cards, loyalty points, etc', 'sort_order' => 44],
            ['code' => '045', 'description' => 'Self-billed - Non-monetary payment to agents, dealers or distributors', 'sort_order' => 45],
        ];

        foreach ($classifications as $classification) {
            \DB::table('item_classifications')->updateOrInsert(
                ['code' => $classification['code']],
                array_merge($classification, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
