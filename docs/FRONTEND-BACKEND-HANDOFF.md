# Controller ที่ต้องทำต่อ

หน้าและ route ฝั่งหน้าร้านมีแล้ว แต่ยังไม่มีข้อมูลร้านค้าจริง ให้ Controller query จาก EER แล้วส่งข้อมูลให้ Blade โดยคง URL และ route name เดิม

| Controller | Route / action | ตาราง EER หลัก |
|---|---|---|
| `HomeController` | `GET /home` — `index` | `product`, ตาราง subtype; คำนวณสินค้าขายดีจาก order |
| `ProductController` | `GET /category/{category?}` — `index`; `GET /product/{category}/{product_id}` — `show` | `product.product_type`, subtype, `product_variant`, `racket_tension`, `review.star/date/comment` |
| `CartController` | `GET /cart_item` — `index`; เพิ่ม/แก้จำนวน/ลบสินค้าในตะกร้า | `cart_item.customer_id/product_id/color/quantity`, `product_variant` |
| `CheckoutController` | `GET /checkout` — `show`; `POST /checkout` — `store` | `delivery_address`, `cart_item`, `orders.shipping`, `order_item.unit_price/quantity`, `payment` |
| `DeliveryAddressController` | `GET/POST /add-address`; `GET/PATCH/DELETE /edit-address/{id}` | `delivery_address` |
| `PaymentMethodController` | `GET /add-credit-card`; การเพิ่ม/ลบวิธีจ่ายผ่านผู้ให้บริการที่ปลอดภัย | `credit_debit_card` และ token/reference จากผู้ให้บริการจ่ายเงิน |
| `FavoriteController` | `GET /favorite` — `index`; เพิ่ม/ลบรายการโปรด | `favorite`, `product` |
| `OrderController` | `GET /status-delivery` — `index`; รายละเอียด order (ถ้าต้องการ) | `orders.order_no/order_date/status/shipping`, `order_item`, `product_variant` |
| `ReviewController` | `GET /review` — `create`; `POST /review` — `store` | `review.customer_id/product_id/order_no/star/date/comment`, `order_item` |

## จุดที่ต้องตกลงก่อนทำ persistence

- Diagram ล่าสุดยังใช้ `customer.customer_id` แต่ Laravel login ปัจจุบันใช้ `users.id`; ห้ามสร้าง customer ซ้ำหรือเปลี่ยน auth จนกว่าจะตกลง mapping/migration
- `racket_tension.tension` เป็น `varchar(20)` ใน diagram; หน้าเว็บรับ tension เป็นจำนวนเต็ม (ขั้นต่ำ 18) และต้องคำนวณค่าสูงสุดจาก tension ของไม้รุ่นนั้น
- Diagram ล่าสุดยังไม่มี `tension` ใน `cart_item`/`order_item`; เพิ่มคอลัมน์และรวมใน key ตามข้อตกลงก่อนเก็บตัวเลือก tension จริง
- `orders` ใน diagram อ้าง customer และเก็บ shipping แต่ยังไม่มี delivery address FK หรือ address snapshot; ต้องตัดสินใจว่าจะเก็บที่อยู่ ณ เวลาสั่งซื้ออย่างไร
- `credit_debit_card` ใช้ composite key `(customer_id, number)` แต่ `payment.card_id` เป็นเลขเดี่ยว; ตกลง reference ให้ตรง และใช้ payment-provider token แทนเก็บเลขบัตรเต็ม/CVV
- View map `product.name/price/image/detail`, `product_variant.color/stock`, `cart_item.quantity`, `order_item.unit_price/quantity`, `delivery_address.address` และ `review.star/date/comment` จาก EER โดยตรง
