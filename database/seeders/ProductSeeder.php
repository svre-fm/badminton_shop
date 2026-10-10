<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    // Unavailable specifications use the requested fallback values.
    private const PRODUCTS = [
        [
            'brand' => 'Kawasaki',
            'model' => 'Dragon Swallow 77 NAVY',
            'type' => 'RACKET',
            'price' => 2390,
            'image' => 'imgs/Rackets/Kawasaki/KAWASAKI-Dragon-Swallow-77-NAVY-BlackOrange-4U-83g-30lbs.png',
            'weight' => '4U',
            'max_tension' => 30,
            'balance' => 'Head Heavy',
            'shaft' => 'Carbon Fiber',
            'flexibility' => 'Stiff',
            'grip_size' => 'G4',
            'colors' => ['Black/Orange'],
        ],
        [
            'brand' => 'Kawasaki',
            'model' => 'Dragon Swallow 77 NAVY',
            'type' => 'RACKET',
            'price' => 2390,
            'image' => 'imgs/Rackets/Kawasaki/KAWASAKI-Dragon-Swallow-77-NAVY-BlueBlack-4U-83g-30lbs.png',
            'weight' => '4U',
            'max_tension' => 30,
            'balance' => 'Head Heavy',
            'shaft' => 'Carbon Fiber',
            'flexibility' => 'Stiff',
            'grip_size' => 'G4',
            'colors' => ['Blue/Black'],
        ],
        [
            'brand' => 'Kawasaki',
            'model' => 'Passion P21',
            'type' => 'RACKET',
            'price' => 1590,
            'image' => 'imgs/Rackets/Kawasaki/KAWASAKI-Passion-P21-Lime-4U-83g-28lbs.png',
            'weight' => '4U',
            'max_tension' => 28,
            'balance' => 'Even Balance',
            'shaft' => 'Graphite',
            'flexibility' => 'Medium',
            'grip_size' => 'G6',
            'colors' => ['Lime'],
        ],
        [
            'brand' => 'Kawasaki',
            'model' => 'Passion P21',
            'type' => 'RACKET',
            'price' => 1590,
            'image' => 'imgs/Rackets/Kawasaki/KAWASAKI-Passion-P21-WhiteOrange-4U-83g-28lbs.png',
            'weight' => '5U',
            'max_tension' => 28,
            'balance' => 'Even Balance',
            'shaft' => 'Graphite',
            'flexibility' => 'Medium',
            'grip_size' => 'G5',
            'colors' => ['White/Orange'],
        ],
        [
            'brand' => 'Kawasaki',
            'model' => 'Victory',
            'type' => 'RACKET',
            'price' => 1190,
            'image' => 'imgs/Rackets/Kawasaki/KAWASAKI-Victory-Blue-3U-94g-26lbs.png',
            'weight' => '3U',
            'max_tension' => 26,
            'balance' => 'Head Light',
            'shaft' => 'Full Carbon',
            'flexibility' => 'Soft',
            'grip_size' => 'G4',
            'colors' => ['Blue'],
        ],
        [
            'brand' => 'Kawasaki',
            'model' => 'Victory',
            'type' => 'RACKET',
            'price' => 1190,
            'image' => 'imgs/Rackets/Kawasaki/KAWASAKI-Victory-Red-3U-94g-26lbs.png',
            'weight' => '4U',
            'max_tension' => 26,
            'balance' => 'Head Light',
            'shaft' => 'Full Carbon',
            'flexibility' => 'Soft',
            'grip_size' => 'G6',
            'colors' => ['Red'],
        ],
        [
            'brand' => 'Victor',
            'model' => 'ARC SABER 11 Pro',
            'type' => 'RACKET',
            'price' => 5990,
            'image' => 'imgs/Rackets/Victor/VICTOR-ARC-SABER-11-Pro-RedBlack-5U-78g-28lbs.png',
            'weight' => '5U',
            'max_tension' => 28,
            'balance' => 'Even Balance',
            'shaft' => 'Carbon Fiber',
            'flexibility' => 'Stiff',
            'grip_size' => 'G5',
            'colors' => ['Red/Black'],
        ],
        [
            'brand' => 'Victor',
            'model' => 'ARS-100X ULTRA',
            'type' => 'RACKET',
            'price' => 4990,
            'image' => 'imgs/Rackets/Victor/VICTOR-ARS-100X-ULTRA-Black-4U-83g-30lbs.png',
            'weight' => '4U',
            'max_tension' => 30,
            'balance' => 'Head Heavy',
            'shaft' => 'Full Carbon',
            'flexibility' => 'Extra Stiff',
            'grip_size' => 'G4',
            'colors' => ['Black'],
        ],
        [
            'brand' => 'Victor',
            'model' => 'BRS-12 PRO',
            'type' => 'RACKET',
            'price' => 3690,
            'image' => 'imgs/Rackets/Victor/VICTOR-BRS-12-PRO-WhiteBlue-4U-83g-28lbs.png',
            'weight' => '3U',
            'max_tension' => 28,
            'balance' => 'Even Balance',
            'shaft' => 'Graphite',
            'flexibility' => 'Medium',
            'grip_size' => 'G6',
            'colors' => ['White/Blue'],
        ],
        [
            'brand' => 'Victor',
            'model' => 'TK-ALPHA M',
            'type' => 'RACKET',
            'price' => 2590,
            'image' => 'imgs/Rackets/Victor/VICTOR-TK-ALPHA-M-LightBlue-4U-83g-30lbs.png',
            'weight' => '5U',
            'max_tension' => 30,
            'balance' => 'Head Light',
            'shaft' => 'Carbon Fiber',
            'flexibility' => 'Soft',
            'grip_size' => 'G6',
            'colors' => ['Light Blue'],
        ],
        [
            'brand' => 'Victor',
            'model' => 'TK-BOOMBA',
            'type' => 'RACKET',
            'price' => 3290,
            'image' => 'imgs/Rackets/Victor/VICTOR-TK-BOOMBA-WhiteBlue-4U-83g-30lbs.png',
            'weight' => '4U',
            'max_tension' => 30,
            'balance' => 'Head Heavy',
            'shaft' => 'Graphite',
            'flexibility' => 'Stiff',
            'grip_size' => 'G5',
            'colors' => ['White/Blue'],
        ],
        [
            'brand' => 'Victor',
            'model' => 'TK-RYUGA II PRO',
            'type' => 'RACKET',
            'price' => 4990,
            'image' => 'imgs/Rackets/Victor/VICTOR-TK-RYUGA-II-PRO-Black-4U-83g-30lbs.png',
            'weight' => '4U',
            'max_tension' => 30,
            'balance' => 'Head Heavy',
            'shaft' => 'Full Carbon',
            'flexibility' => 'Extra Stiff',
            'grip_size' => 'G4',
            'colors' => ['Black'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'ASTROX 01 ABILITY',
            'type' => 'RACKET',
            'price' => 1590,
            'image' => 'imgs/Rackets/Yonex/YONEX-ASTROX-01-ABILITY-Orange-4U-83g-28lbs.png',
            'weight' => '4U',
            'max_tension' => 28,
            'balance' => 'Head Heavy',
            'shaft' => 'Carbon Fiber',
            'flexibility' => 'Medium',
            'grip_size' => 'G5',
            'colors' => ['Orange'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'ASTROX 77 PRO',
            'type' => 'RACKET',
            'price' => 6290,
            'image' => 'imgs/Rackets/Yonex/YONEX-ASTROX-77-PRO-YellowBlack-4U-83g-27lbs.png',
            'weight' => '4U',
            'max_tension' => 27,
            'balance' => 'Head Heavy',
            'shaft' => 'Graphite',
            'flexibility' => 'Stiff',
            'grip_size' => 'G5',
            'colors' => ['Yellow/Black'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'ASTROX 100ZZ',
            'type' => 'RACKET',
            'price' => 7290,
            'image' => 'imgs/Rackets/Yonex/YONEX-ASTROX-100ZZ-BlackCherry-4U-28lbs.png',
            'weight' => '4U',
            'max_tension' => 28,
            'balance' => 'Head Heavy',
            'shaft' => 'Full Carbon',
            'flexibility' => 'Extra Stiff',
            'grip_size' => 'G5',
            'colors' => ['Black/Cherry'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'NANOFLARE 001 CLEAR',
            'type' => 'RACKET',
            'price' => 1490,
            'image' => 'imgs/Rackets/Yonex/YONEX-NANOFLARE-001-CLEAR-LightBlue-5U-78g-27lbs.png',
            'weight' => '5U',
            'max_tension' => 27,
            'balance' => 'Head Light',
            'shaft' => 'Graphite',
            'flexibility' => 'Medium',
            'grip_size' => 'G4',
            'colors' => ['Light Blue'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'NANOFLARE 001 CLEAR',
            'type' => 'RACKET',
            'price' => 1490,
            'image' => 'imgs/Rackets/Yonex/YONEX-NANOFLARE-001-CLEAR-RedBlack-5U-78g-27lbs.png',
            'weight' => '4U',
            'max_tension' => 27,
            'balance' => 'Head Light',
            'shaft' => 'Graphite',
            'flexibility' => 'Medium',
            'grip_size' => 'G6',
            'colors' => ['Red/Black'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'NANOFLARE 001 CLEAR',
            'type' => 'RACKET',
            'price' => 1490,
            'image' => 'imgs/Rackets/Yonex/YONEX-NANOFLARE-001-CLEAR-WhiteAqua-5U-78g-27lbs.png',
            'weight' => '5U',
            'max_tension' => 27,
            'balance' => 'Head Light',
            'shaft' => 'Graphite',
            'flexibility' => 'Medium',
            'grip_size' => 'G5',
            'colors' => ['White/Aqua'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'NANOFLARE 1000 Play',
            'type' => 'RACKET',
            'price' => 2890,
            'image' => 'imgs/Rackets/Yonex/YONEX-NANOFLARE-1000-Play-BlackYellow-4U-83g-28lbs.png',
            'weight' => '4U',
            'max_tension' => 28,
            'balance' => 'Head Light',
            'shaft' => 'Carbon Fiber',
            'flexibility' => 'Stiff',
            'grip_size' => 'G4',
            'colors' => ['Black/Yellow'],
        ],
        [
            'brand' => 'Kuikma',
            'model' => 'Towel Grip',
            'type' => 'GRIP',
            'price' => 150,
            'image' => 'imgs/Grip/Decathlon-TowelGrip-White.png',
            'detail' => 'กริปผ้า Kuikma ซับเหงื่อได้ดี ช่วยให้จับด้ามได้กระชับระหว่างเล่น บรรจุ 2 ชิ้น เหมาะกับผู้เล่นที่ต้องการเปลี่ยนกริปเป็นประจำ',
            'spec' => ['type' => 'Towel grip', 'material' => 'Cotton towel'],
            'colors' => ['White', 'Yellow'],
        ],
        [
            'brand' => 'SSPORT',
            'model' => '02',
            'type' => 'GRIP',
            'price' => 59,
            'image' => 'imgs/Grip/SSPORT-02BK.png',
            'detail' => 'กริปพันด้าม SSPORT รุ่น 02 ช่วยเพิ่มชั้นสัมผัสระหว่างมือกับด้ามไม้ มีสีให้เลือกตามภาพ เหมาะกับผู้เล่นที่ต้องการเปลี่ยนสีหรือพันกริปใหม่',
            'spec' => ['type' => 'Overgrip', 'material' => 'Synthetic'],
            'colors' => ['Black', 'Blue', 'Orange'],
        ],
        [
            'brand' => 'Victor',
            'model' => 'GR253',
            'type' => 'GRIP',
            'price' => 120,
            'image' => 'imgs/Grip/VICTOR-GR253-Red.png',
            'detail' => 'กริปพันด้าม Victor GR253 สีแดง ช่วยปรับสัมผัสของด้ามและเพิ่มความมั่นใจในการจับ เหมาะกับผู้เล่นที่ต้องการเปลี่ยนกริปเดิมให้มีสีสัน',
            'spec' => ['type' => 'Overgrip', 'material' => 'Synthetic'],
            'colors' => ['Red'],
        ],
        [
            'brand' => 'Victor',
            'model' => 'GR262',
            'type' => 'GRIP',
            'price' => 120,
            'image' => 'imgs/Grip/VICTOR-GR262-Yellow.png',
            'detail' => 'กริปพันด้าม Victor GR262 สีเหลือง ช่วยปรับสัมผัสของด้ามและเพิ่มความมั่นใจในการจับ เหมาะกับผู้เล่นที่ต้องการเปลี่ยนกริปเดิมให้มีสีสัน',
            'spec' => ['type' => 'Overgrip', 'material' => 'Synthetic'],
            'colors' => ['Yellow'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'AC102EX',
            'type' => 'GRIP',
            'price' => 100,
            'image' => 'imgs/Grip/YONEX-AC102EX-Black.png',
            'detail' => 'Yonex Wet Super Grap AC102EX เป็นกริปชนิด wet ตามข้อมูลบนบรรจุภัณฑ์ ให้สัมผัสหนึบสำหรับการจับด้าม เหมาะกับผู้เล่นที่ชอบกริปสัมผัสชุ่มมือ',
            'spec' => ['type' => 'Wet overgrip', 'material' => 'Synthetic'],
            'colors' => ['Black', 'Green', 'Orange', 'Pink', 'Red', 'White', 'Yellow'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'AC108EX Super Grap Pure',
            'type' => 'GRIP',
            'price' => 250,
            'image' => 'imgs/Grip/YONEX-AC108EX.png',
            'detail' => 'Yonex AC108EX Super Grap Pure เป็นกริปพันด้ามสังเคราะห์สีดำ ช่วยเพิ่มชั้นสัมผัสบนด้ามไม้ เหมาะกับผู้เล่นที่ต้องการกริปเปลี่ยนสำหรับใช้งานทั่วไป',
            'spec' => ['type' => 'Overgrip', 'material' => 'Synthetic'],
            'colors' => ['Black'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'AC135EX Strong Grap',
            'type' => 'GRIP',
            'price' => 210,
            'image' => 'imgs/Grip/YONEX-AC135EX.png',
            'detail' => 'Yonex AC135EX Strong Grap เป็นกริปสังเคราะห์สำหรับพันด้ามมาตรฐาน ภาพแสดงบรรจุภัณฑ์ 3 ชิ้น เหมาะกับผู้เล่นที่ต้องการเตรียมกริปสำรองไว้เปลี่ยน',
            'spec' => ['type' => 'Replacement grip', 'material' => 'Synthetic'],
            'colors' => ['Black'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'Dry Super Grap AC149-3EX',
            'type' => 'GRIP',
            'price' => 100,
            'image' => 'imgs/Grip/YONEX-Dry-Super-Grap.png',
            'detail' => 'Yonex Dry Super Grap เป็นกริปชนิด dry ที่ระบุคุณสมบัติการซับเหงื่อบนบรรจุภัณฑ์ ช่วยให้จับด้ามได้เหมาะกับการเล่นที่มีเหงื่อ เหมาะกับผู้เล่นที่ชอบสัมผัสแบบแห้ง',
            'spec' => ['type' => 'Dry overgrip', 'material' => 'Synthetic'],
            'colors' => ['White'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'Aerosonic',
            'type' => 'STRING',
            'price' => 390,
            'image' => 'imgs/String/YONEX-Aerosonic-61-White.png',
            'detail' => 'เอ็น Yonex Aerosonic ขนาด 0.61 มม. ระบุ Repulsion Power และ Medium Feeling บนซอง พร้อมโครงสร้าง Multifilament เหมาะกับผู้เล่นที่มองหาเอ็นเส้นเล็กและสัมผัสการตีระดับกลาง',
            'spec' => ['thickness' => '0.61 mm', 'string_characteristic' => 'Repulsion power; medium feeling; multifilament'],
            'colors' => ['White'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'BG 65 Titanium',
            'type' => 'STRING',
            'price' => 250,
            'image' => 'imgs/String/YONEX-BG-65-Titanium-White.png',
            'detail' => 'เอ็น Yonex BG 65 Titanium ขนาด 0.70 มม. ระบุ Durability และ Hard Feeling บนซอง พร้อมโครงสร้าง Multifilament เหมาะกับผู้เล่นที่ต้องการเอ็นสำหรับใช้งานต่อเนื่อง',
            'spec' => ['thickness' => '0.70 mm', 'string_characteristic' => 'Durability; hard feeling; multifilament'],
            'colors' => ['White'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'BG 66 Force',
            'type' => 'STRING',
            'price' => 350,
            'image' => 'imgs/String/YONEX-BG-66-Force-White.png',
            'detail' => 'เอ็น Yonex BG 66 Force ขนาด 0.65 มม. ระบุ Repulsion Power และ Medium Feeling บนซอง พร้อมโครงสร้าง Multifilament เหมาะกับผู้เล่นที่ชอบสัมผัสเอ็นระดับกลาง',
            'spec' => ['thickness' => '0.65 mm', 'string_characteristic' => 'Repulsion power; medium feeling; multifilament'],
            'colors' => ['White'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'BG 66 Ultimax',
            'type' => 'STRING',
            'price' => 350,
            'image' => 'imgs/String/YONEX-BG-66-Ultimax-Blue.png',
            'detail' => 'เอ็น Yonex BG 66 Ultimax ขนาด 0.65 มม. ระบุ Quick Repulsion บนบรรจุภัณฑ์ และบางสีแสดง Repulsion Power, Medium Feeling และ Multifilament เพิ่มเติม เหมาะกับผู้เล่นที่มองหาเอ็นสำหรับเกมตอบโต้คล่องตัว',
            'spec' => ['thickness' => '0.65 mm', 'string_characteristic' => 'Quick repulsion; repulsion power; medium feeling'],
            'colors' => ['Blue', 'Green', 'Yellow'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'BG 80 Power',
            'type' => 'STRING',
            'price' => 390,
            'image' => 'imgs/String/YONEX-BG-80-Power-White.png',
            'detail' => 'เอ็น Yonex BG 80 Power ระบุ Repulsion Power, Quick Repulsion และ Powerful Smash บนซอง เหมาะกับผู้เล่นที่มองหาเอ็นสำหรับจังหวะบุกและการตีตบ',
            'spec' => ['thickness' => '0.68 mm', 'string_characteristic' => 'Repulsion power; quick repulsion; powerful smash'],
            'colors' => ['White'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'BG 80',
            'type' => 'STRING',
            'price' => 390,
            'image' => 'imgs/String/YONEX-BG-80-White.png',
            'detail' => 'เอ็น Yonex BG 80 ขนาด 0.68 มม. ระบุ Repulsion Power และ Hard Feeling บนซอง พร้อมโครงสร้าง Multifilament เหมาะกับผู้เล่นที่ชอบสัมผัสเอ็นแน่น',
            'spec' => ['thickness' => '0.68 mm', 'string_characteristic' => 'Repulsion power; hard feeling; multifilament'],
            'colors' => ['White'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'Exbolt 65',
            'type' => 'STRING',
            'price' => 390,
            'image' => 'imgs/String/YONEX-Exbolt-65-White.png',
            'detail' => 'เอ็น Yonex Exbolt 65 ขนาด 0.65 มม. ระบุ Quick Repulsion และแสดงหัวข้อ Durability, Hitting Sound, Shock Absorption และ Control บนซอง เหมาะกับผู้เล่นที่ต้องการเอ็นสำหรับเกมคล่องตัว',
            'spec' => ['thickness' => '0.65 mm', 'string_characteristic' => 'Quick repulsion; durability; hitting sound; shock absorption; control'],
            'colors' => ['White'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'Nanogy 98',
            'type' => 'STRING',
            'price' => 350,
            'image' => 'imgs/String/YONEX-Nanogy-98-Lime.png',
            'detail' => 'เอ็น Yonex Nanogy 98 ขนาด 0.66 มม. ระบุ Repulsion Power และ Medium Feeling พร้อมเทคโนโลยี Nanoscience บนซอง เหมาะกับผู้เล่นที่ต้องการสัมผัสเอ็นระดับกลาง',
            'spec' => ['thickness' => '0.66 mm', 'string_characteristic' => 'Repulsion power; medium feeling; Nanosci CS carbon nanotube'],
            'colors' => ['Lime'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'Skyarc',
            'type' => 'STRING',
            'price' => 350,
            'image' => 'imgs/String/YONEX-Skyarc-White.png',
            'detail' => 'เอ็น Yonex Skyarc ขนาด 0.69 มม. ระบุ Control และการเร่งลูกเคลียร์จากท้ายคอร์ตบนซอง พร้อมแสดงหัวข้อ Repulsion, Durability, Hitting Sound และ Shock Absorption เหมาะกับผู้เล่นที่เน้นควบคุมลูก',
            'spec' => ['thickness' => '0.69 mm', 'string_characteristic' => 'Control; quick repulsion; durability; hitting sound; shock absorption'],
            'colors' => ['White'],
        ],
        [
            'brand' => 'Kawasaki',
            'model' => 'B1 76',
            'type' => 'SHUTTLECOCK',
            'price' => 650,
            'image' => 'imgs/ShuttleCock/KAWASAKI-B1-76.png',
            'detail' => 'ลูกแบดมินตัน Kawasaki B1 สำหรับการฝึกซ้อมและเล่นทั่วไป รูปแบบขนไก่ช่วยให้เห็นทิศทางลูกได้ชัด เหมาะกับผู้เล่นที่ต้องการลูกสำหรับเล่นในสนามในร่ม',
            'material' => 'Feather',
            'speed' => '77',
            'colors' => ['White'],
        ],
        [
            'brand' => 'Vension',
            'model' => 'Bullet 76',
            'type' => 'SHUTTLECOCK',
            'price' => 650,
            'image' => 'imgs/ShuttleCock/VENSION-Bullet-76.png',
            'detail' => 'ลูกแบดมินตัน Vension Bullet สำหรับการฝึกซ้อมและเล่นทั่วไป รูปแบบขนไก่เหมาะกับการเล่นในสนามในร่ม เหมาะกับผู้เล่นที่ต้องการลูกสำหรับเกมประจำวัน',
            'material' => 'Feather',
            'speed' => '75',
            'colors' => ['White'],
        ],
        [
            'brand' => 'Victor',
            'model' => 'NS-3000',
            'type' => 'SHUTTLECOCK',
            'price' => 450,
            'image' => 'imgs/ShuttleCock/VICTOR-NS-3000.png',
            'detail' => 'ลูกแบดมินตันไนลอน Victor NS-3000 สีเหลืองตามภาพ ดูแลรักษาง่ายสำหรับการฝึกซ้อม เหมาะกับผู้เล่นที่ต้องการลูกไนลอนสำหรับเล่นในสนามในร่ม',
            'material' => 'Nylon',
            'speed' => '78',
            'colors' => ['Yellow'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'Aeroclear',
            'type' => 'SHUTTLECOCK',
            'price' => 550,
            'image' => 'imgs/ShuttleCock/YONEX-Aeroclear.png',
            'detail' => 'ลูกแบดมินตัน Yonex Aeroclear สำหรับการเล่นในสนามในร่ม รูปแบบขนไก่ช่วยให้มองเห็นแนวทางลูกขณะเล่น เหมาะกับการฝึกซ้อมและเล่นทั่วไป',
            'material' => 'Feather',
            'speed' => '76',
            'colors' => ['White'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'Aerosensa 350',
            'type' => 'SHUTTLECOCK',
            'price' => 850,
            'image' => 'imgs/ShuttleCock/YONEX-Aerosensa-350.png',
            'detail' => 'ลูกแบดมินตันขนไก่ Yonex Aerosensa 350 สำหรับการเล่นในสนามในร่ม เหมาะกับผู้เล่นที่ต้องการลูกขนไก่สำหรับการฝึกซ้อมหรือเล่นเป็นประจำ',
            'material' => 'Feather',
            'speed' => '77',
            'colors' => ['White'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'League',
            'type' => 'SHUTTLECOCK',
            'price' => 650,
            'image' => 'imgs/ShuttleCock/YONEX-League.png',
            'detail' => 'ลูกแบดมินตันขนไก่ Yonex League สำหรับการเล่นในสนามในร่ม เหมาะกับผู้เล่นที่ต้องการลูกสำหรับซ้อมและเล่นทั่วไป เลือกความเร็วให้เหมาะกับอุณหภูมิและสภาพสนาม',
            'material' => 'Feather',
            'speed' => '75',
            'colors' => ['White'],
        ],
        [
            'brand' => 'Yonex',
            'model' => 'MAVIS 350',
            'type' => 'SHUTTLECOCK',
            'price' => 450,
            'image' => 'imgs/ShuttleCock/YONEX-MAVIS-350.png',
            'detail' => 'ลูกแบดมินตันไนลอน Yonex MAVIS 350 สีเหลืองตามภาพ ใช้งานสะดวกสำหรับการฝึกซ้อมและเล่นทั่วไป เหมาะกับผู้เล่นที่มองหาลูกไนลอนสำหรับสนามในร่ม',
            'material' => 'Nylon',
            'speed' => '78',
            'colors' => ['Yellow'],
        ],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $productIds = DB::table('products')->pluck('product_id');

            if ($productIds->isNotEmpty()) {
                $orderNumbers = DB::table('order_items')
                    ->whereIn('product_id', $productIds)
                    ->distinct()
                    ->pluck('order_no');

                if ($orderNumbers->isNotEmpty()) {
                    DB::table('payments')->whereIn('order_no', $orderNumbers)->delete();
                    DB::table('reviews')->whereIn('order_no', $orderNumbers)->delete();
                    DB::table('orders')->whereIn('order_no', $orderNumbers)->delete();
                }

                DB::table('order_items')->whereIn('product_id', $productIds)->delete();
                DB::table('products')->whereIn('product_id', $productIds)->delete();
            }

            foreach (self::PRODUCTS as $definition) {
                $this->insertProduct($definition);
            }
        });
    }

    private function insertProduct(array $definition): void
    {
        $name = $this->productName($definition);
        $now = now();

        $productId = (int) DB::table('products')->insertGetId([
            'name' => $name,
            'brand' => strtoupper($definition['brand']),
            'detail' => $definition['detail'] ?? $this->racketDescription($definition),
            'price' => $definition['price'],
            'image' => $definition['image'],
            'product_type' => $definition['type'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ($definition['colors'] as $color) {
            DB::table('productvariants')->insert([
                'product_id' => $productId,
                'color' => $color,
                'stock' => 20,
            ]);
        }

        $this->insertSubtype($productId, $definition);
    }

    private function productName(array $definition): string
    {
        $brand = strtoupper($definition['brand']);

        return match ($definition['type']) {
            'RACKET' => sprintf(
                '%s - %s %s - %s - %d lbs - %s',
                $brand,
                $definition['model'],
                $definition['colors'][0],
                $definition['weight'],
                $definition['max_tension'],
                $definition['grip_size'],
            ),
            'SHUTTLECOCK' => sprintf(
                '%s - %s (%s speed)',
                $brand,
                $definition['model'],
                $definition['speed'],
            ),
            default => sprintf('%s - %s', $brand, $definition['model']),
        };
    }

    private function racketDescription(array $definition): string
    {
        $name = $this->productName($definition);

        return sprintf(
            'ไม้แบดมินตัน %s น้ำหนัก %s ช่วยให้เลือกจังหวะและน้ำหนักไม้ได้ตามสไตล์การเล่น รองรับความตึงเอ็นสูงสุด %d ปอนด์ เหมาะสำหรับผู้เล่นที่มองหาไม้รุ่นนี้เพื่อฝึกซ้อมหรือเล่นทั่วไป',
            $name,
            $definition['weight'],
            $definition['max_tension'],
        );
    }

    private function insertSubtype(int $productId, array $definition): void
    {
        match ($definition['type']) {
            'RACKET' => $this->insertRacket($productId, $definition),
            'STRING' => DB::table('badminton_strings')->insert([
                'product_id' => $productId,
                ...$definition['spec'],
            ]),
            'GRIP' => DB::table('grips')->insert([
                'product_id' => $productId,
                ...$definition['spec'],
            ]),
            'SHUTTLECOCK' => DB::table('shuttlecocks')->insert([
                'product_id' => $productId,
                'type' => $definition['material'],
                'speed_rating' => $definition['speed'],
            ]),
        };
    }

    private function insertRacket(int $productId, array $definition): void
    {
        DB::table('badminton_rackets')->insert([
            'product_id' => $productId,
            'balance_point' => $definition['balance'],
            'shaft' => $definition['shaft'],
            'flexibility' => $definition['flexibility'],
            'weight' => $definition['weight'],
            'grip_size' => $definition['grip_size'],
        ]);

        $min = $definition['max_tension'] - 10;
        $lowEnd = $min + 3;
        $mediumEnd = $min + 7;

        foreach ([
            sprintf('Low %d-%d lbs', $min, $lowEnd),
            sprintf('Medium %d-%d lbs', $lowEnd + 1, $mediumEnd),
            sprintf('High %d-%d lbs', $mediumEnd + 1, $definition['max_tension']),
        ] as $range) {
            DB::table('racket_tensions')->insert([
                'product_id' => $productId,
                'tension' => $range,
            ]);
        }
    }
}
