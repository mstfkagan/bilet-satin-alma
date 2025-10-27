<?php
require_once __DIR__ . '/../src/flow_guard.php';
require_once __DIR__ . '/../src/auth.php';
requireAuth(['user']);
require_once __DIR__ . '/../src/db.php';

$trip_id = $_GET['id'] ?? null;
if (!$trip_id) { header('Location: index.php'); exit; }
$db = getDB();

$stmt = $db->prepare('SELECT t.*, bc.name as company_name FROM Trips t JOIN Bus_Company bc ON t.company_id = bc.id WHERE t.id = ?');
$stmt->execute([$trip_id]);
$trip = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trip) { header('Location: index.php'); exit; }

$stmt = $db->prepare('SELECT bs.seat_number FROM Booked_Seats bs JOIN Tickets t ON bs.ticket_id = t.id WHERE t.trip_id = ?');
$stmt->execute([$trip_id]);
$booked_seats = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

// otobüs koltuk sayısını firmanın ayarladığı kadar ayarlama
$total_seats = (int)$trip['capacity'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Koltuk Seçimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .seat { width: 45px; height: 45px; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; cursor: pointer; border-radius: 5px; user-select: none; position: relative; font-weight: bold; }
    .seat.aisle { border: none; cursor: default; background-color: transparent !important; }
    .seat.booked { background-color: #dc3545; color: white; cursor: not-allowed; }
    .seat.available { background-color: #198754; color: white; }
    .seat.available:hover { background-color: #157347; }
    .seat.selected { background-color: rgba(13, 110, 253, 0.5); color: white; border-color: #0d6efd; }
    .seat.selected::after { content: 'X'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 2em; color: rgba(255, 255, 255, 0.7); pointer-events: none; }
    .bus-layout { max-width: 300px; margin: auto; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="index.php" class="btn btn-outline-secondary">← Seferlere Dön</a>
        <h2>Koltuk Seçimi</h2>
        <span>👋 <?= htmlspecialchars($_SESSION['name']) ?></span>
    </div>

    <div class="card">
        <div class="card-header">
            <strong><?= htmlspecialchars($trip['company_name']) ?></strong> -
            <?= htmlspecialchars($trip['departure_city']) ?> → <?= htmlspecialchars($trip['destination_city']) ?>
            (<?= htmlspecialchars(date("d.m.Y H:i", strtotime($trip['departure_time']))) ?>)
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-center mb-3">Otobüs Planı</h5>
                    <div class="p-3 bg-white border rounded">
                        <form id="seat-form" action="buy_ticket.php" method="POST">
                            <input type="hidden" name="trip_id" value="<?= htmlspecialchars($trip_id) ?>">
                            <div class="row g-2 bus-layout">
                                <?php for ($i = 1; $i <= $total_seats; $i++): ?>
                                    <?php
                                    $isBooked = in_array($i, $booked_seats);
                                    $seatClass = $isBooked ? 'booked' : 'available';
                                    ?>
                                    <div class="col-3">
                                        <label class="seat <?= $seatClass ?>" data-seat-number="<?= $i ?>">
                                            <?php if (!$isBooked): ?>
                                                <input type="checkbox" name="seats[]" value="<?= $i ?>" class="d-none seat-checkbox">
                                            <?php endif; ?>
                                            <?= $i ?>
                                        </label>
                                    </div>
                                    <?php if ($i % 3 == 1 && $i < $total_seats): ?>
                                        <div class="col-3">
                                            <div class="seat aisle"></div>
                                        </div>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-6">
                    <h5 class="text-center mb-3">Seçim Özeti</h5>
                    <div class="p-3 border rounded bg-white">
                        <p>Birim Fiyat: <strong id="price-per-seat"><?= htmlspecialchars($trip['price']) ?></strong> ₺</p>
                        <hr>
                        <p>Seçilen Koltuklar: <strong id="selected-seats-text">Henüz seçilmedi</strong></p>
                        <p>Ara Toplam: <strong id="sub-total-price">0</strong> ₺</p>
                        <div id="discount-row" class="text-success d-none">
                            <p>İndirim (<span id="discount-rate">0</span>%): <strong id="discount-amount">0</strong> ₺</p>
                        </div>
                        <hr>
                        <p class="h4">Toplam Tutar: <strong id="total-price">0</strong> ₺</p>
                        
                        <div class="mt-3">
                            <form id="coupon-form">
                                <label class="form-label">İndirim Kodu</label>
                                <div class="input-group">
                                    <input type="text" id="coupon-code" name="coupon_code" class="form-control" placeholder="Kupon Kodu">
                                    <button class="btn btn-outline-secondary" type="submit">Uygula</button>
                                </div>
                            </form>
                            <div id="coupon-message" class="mt-2"></div>
                        </div>

                        <button type="submit" form="seat-form" class="btn btn-success w-100 mt-3" disabled id="buy-button">Satın Al</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const seatContainer = document.getElementById('seat-form');
    const pricePerSeat = parseFloat(document.getElementById('price-per-seat').textContent);
    const selectedSeatsText = document.getElementById('selected-seats-text');
    const subTotalPriceText = document.getElementById('sub-total-price');
    const totalPriceText = document.getElementById('total-price');
    const buyButton = document.getElementById('buy-button');
    const couponForm = document.getElementById('coupon-form');
    const couponCodeInput = document.getElementById('coupon-code');
    const couponMessage = document.getElementById('coupon-message');
    const discountRow = document.getElementById('discount-row');
    const discountRateText = document.getElementById('discount-rate');
    const discountAmountText = document.getElementById('discount-amount');
    
    let appliedDiscount = 0;

    const seatForm = document.getElementById('seat-form');
    let hiddenCouponInput = document.createElement('input');
    hiddenCouponInput.type = 'hidden';
    hiddenCouponInput.name = 'applied_coupon';
    seatForm.appendChild(hiddenCouponInput);

    seatContainer.addEventListener('click', (e) => {
        const seatLabel = e.target.closest('.seat.available');
        if (!seatLabel) return;
        const checkbox = seatLabel.querySelector('.seat-checkbox');
        checkbox.checked = !checkbox.checked;
        seatLabel.classList.toggle('selected');
        updateSummary();
    });

    couponForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const code = couponCodeInput.value.trim();
        if (!code) return;

        const formData = new FormData();
        formData.append('coupon_code', code);
        formData.append('trip_id', '<?= $trip_id ?>');

        fetch('apply_coupon.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            couponMessage.className = data.success ? 'text-success' : 'text-danger';
            couponMessage.textContent = data.message;
            
            if (data.success) {
                appliedDiscount = parseFloat(data.discount);
                hiddenCouponInput.value = code;
            } else {
                appliedDiscount = 0;
                hiddenCouponInput.value = '';
            }
            updateSummary();
        });
    });

    function updateSummary() {
        const selectedCheckboxes = document.querySelectorAll('.seat-checkbox:checked');
        const selectedSeatNumbers = Array.from(selectedCheckboxes).map(cb => cb.value);
        
        const seatCount = selectedSeatNumbers.length;
        const subTotal = seatCount * pricePerSeat;
        const discountAmount = subTotal * (appliedDiscount / 100);
        const total = subTotal - discountAmount;

        if (seatCount > 0) {
            selectedSeatsText.textContent = selectedSeatNumbers.join(', ');
            buyButton.disabled = false;
        } else {
            selectedSeatsText.textContent = 'Henüz seçilmedi';
            buyButton.disabled = true;
        }
        
        subTotalPriceText.textContent = subTotal.toFixed(2);
        
        if (appliedDiscount > 0 && seatCount > 0) {
            discountRateText.textContent = appliedDiscount;
            discountAmountText.textContent = "- " + discountAmount.toFixed(2);
            discountRow.classList.remove('d-none');
        } else {
            discountRow.classList.add('d-none');
        }

        totalPriceText.textContent = total.toFixed(2);
    }
});
</script>
</body>
</html>