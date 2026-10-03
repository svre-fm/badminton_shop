# 1. Clone โปรเจกต์
git clone ใน Ubuntu
```
git clone https://github.com/svre-fm/badminton_shop.git
cd badminton_shop
```

# 2. สร้างไฟล์ .env
copy .env.example ลง .env

# 3. ติดตั้ง PHP packages 
```
docker run --rm -u "$(id -u):$(id -g)" \
  -v "$(pwd):/var/www/html" -w /var/www/html \
  laravelsail/php84-composer:latest composer install --ignore-platform-reqs
```

# 4. เปิด containers (เว็บ + MySQL)
```
./vendor/bin/sail up -d
```

# 5. สร้าง APP_KEY
```
./vendor/bin/sail artisan key:generate
```

# 6. ติดตั้งและ build frontend
```
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

# 7. สร้างตารางในฐานข้อมูล (+ ข้อมูลตัวอย่างถ้ามี seeder)
```
./vendor/bin/sail artisan migrate:fresh --seed
```