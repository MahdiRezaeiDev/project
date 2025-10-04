<?php
$pageTitle = "قیمت دستوری";
$iconUrl = 'ordered.png';
require_once './components/header.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';

$updateSuccess = false;

// Update script
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id      = intval($_POST['id']);
    $percent = intval($_POST['percent']);
    $benefit = intval($_POST['benefit']);
    $status  = intval($_POST['status']);

    $sql = PDO_CONNECTION->prepare("UPDATE hussain_api SET percent = ?, benefit = ?, status = ? WHERE id = ?");
    $sql->execute([$percent, $benefit, $status, $id]);

    $updateSuccess = true;
}

// Fetch data
$stmt = PDO_CONNECTION->prepare("SELECT * FROM hussain_api");
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
$qualified = ['mahdi', 'babak', 'niyayesh'];

?>

<style>
    body {
        font-family: sans-serif;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
    }

    th {
        background: #f5f5f5;
    }

    button {
        padding: 5px 10px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-edit {
        background: #3498db;
        color: #fff;
    }

    .btn-cancel {
        background: #7f8c8d;
        color: #fff;
    }

    .btn-save {
        background: #27ae60;
        color: #fff;
    }

    /* Sidebar */
    #hussainSidebar {
        position: fixed;
        left: -420px;
        top: 0;
        width: 400px;
        height: 100%;
        background: #f3f4f6;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
        padding: 20px;
        overflow-y: auto;
        transition: left 0.3s ease;
        z-index: 9999;
    }

    #hussainSidebar.open {
        left: 0;
    }

    /* Overlay */
    #sidebarOverlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.3);
        z-index: 9998;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
        z-index: 10000;
    }

    .modal-content {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        width: 400px;
    }

    .modal-header {
        font-size: 18px;
        margin-bottom: 15px;
        font-weight: bold;
    }

    .form-group {
        margin-bottom: 12px;
        text-align: right;
    }

    .form-group label {
        display: block;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 6px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    /* Custom alert */
    #customAlert {
        display: none;
        position: fixed;
        bottom: 60px;
        right: 16px;
        background: #27ae60;
        color: #fff;
        padding: 12px 20px;
        border-radius: 5px;
        z-index: 11000;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        font-weight: bold;
    }
</style>

<!-- Custom alert -->
<div id="customAlert">تنظیمات با موفقیت به‌روزرسانی شد!</div>

<?php if (in_array($_SESSION['username'], $qualified)): ?><!-- Sidebar toggle button -->
    <button onclick="toggleSidebar()"
        style="position:fixed; top:60px; right:16px; z-index:10001; background:#4f46e5; color:#fff; padding:8px 12px; border-radius:5px;">
        تنظیمات حسین پارت
    </button>
<?php endif; ?>

<!-- Sidebar -->
<div id="hussainSidebar">
    <h2 style="text-align:center; margin-bottom:15px;">تنظیمات حسین پارت</h2>
    <table>
        <thead>
            <tr>
                <th>درصد</th>
                <th>حاشیه سود</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result) { ?>
                <tr>
                    <td><?= $result['percent'] ?></td>
                    <td><?= $result['benefit'] ?></td>
                    <td><?= $result['status'] == 1 ? "فعال" : "غیرفعال" ?></td>
                    <td>
                        <button class="btn-edit"
                            onclick="openModal(
                                '<?= $result['id'] ?>',
                                '<?= $result['percent'] ?>',
                                '<?= $result['benefit'] ?>',
                                '<?= $result['status'] ?>'
                            )">ویرایش</button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Sidebar overlay -->
<div id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">ویرایش تنظیمات حسین پارت</div>
        <form method="POST" action="" dir="rtl">
            <input type="hidden" name="id" id="record_id">
            <div class="form-group">
                <label>درصد:</label>
                <input type="number" name="percent" id="percent">
            </div>
            <div class="form-group">
                <label>حاشیه سود:</label>
                <input type="number" name="benefit" id="benefit">
            </div>
            <div class="form-group">
                <label>وضعیت:</label>
                <select name="status" id="status">
                    <option value="1">فعال</option>
                    <option value="0">غیر فعال</option>
                </select>
            </div>
            <div style="text-align:right; margin-top: 10px;">
                <button type="button" class="btn-cancel" onclick="closeModal()">لغو</button>
                <button type="submit" class="btn-save">ثبت</button>
            </div>
        </form>
    </div>
</div>

<div class="max-w-2xl mx-auto py-14 px-6 lg:px-8 bg-gray-200 rounded-lg shadow-s mt-32">
    <form target="_blank" action="./orderedPrice.php" method="post">
        <input type="text" name="givenPrice" value="givenPrice" id="form" hidden>
        <input type="text" name="user" value="<?= $_SESSION["id"] ?>" hidden>
        <input type="text" name="customer" value="1" id="target_customer" hidden>
        <div class="">
            <div class="col-span-6 sm:col-span-4">
                <label for="code" class="block text-lg font-semibold text-gray-900">
                    کدهای مدنظر
                </label>
                <textarea onkeyup="convertToEnglish(this)" onchange="filterCode(this)" rows="9" id="code" name="code" required
                    class="border-2 border-gray-300 focus:border-gray-500 p-3 outline-none text-sm mt-1 shadow-sm block w-full uppercase"
                    style="direction: ltr !important;"
                    placeholder="لطفا کد های مود نظر خود را در خط های مجزا قرار دهید"></textarea>
            </div>
        </div>
        <div class="py-4 flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="discount" value="0">
            <input id="discount" type="checkbox" name="discount" value="1">
            <label for="discount" class="text-sm">اعمال تخفیف</label>
        </div>

        <div class="flex items-center justify-between py-3 text-right sm:rounded-bl-md sm:rounded-br-md">
            <div class="flex gap-2 items-center">
                <button type="submit" formaction="../factor/createPreCompleteBill.php?partner=0"
                    class="cursor-pointer text-white rounded bg-sky-600 hover:bg-sky-500 px-3 py-2 text-xs">پیش فاکتور مصرف کننده</button>
                <button type="submit" formaction="../factor/createPreCompleteBill.php?partner=1"
                    class="cursor-pointer bg-green-600 hover:bg-green-700 text-white rounded px-3 py-2 text-xs">پیش فاکتور همکار</button>
                <button type="submit" formaction="../factor/createPreCompleteBill.php?partner=1&insurance=1"
                    class="cursor-pointer bg-rose-600 hover:bg-rose-700 text-white rounded px-3 py-2 text-xs">امانت نامه</button>
            </div>
            <button type="submit" class="inline-flex items-center px-5 py-2 bg-gray-800 font-semibold text-xs text-white hover:bg-gray-700 rounded">جستجو</button>
        </div>
    </form>
</div>

<script>
    function toggleSidebar() {
        let sidebar = document.getElementById('hussainSidebar');
        let overlay = document.getElementById('sidebarOverlay');
        if (sidebar.classList.contains('open')) {
            sidebar.classList.remove('open');
            overlay.style.display = 'none';
        } else {
            sidebar.classList.add('open');
            overlay.style.display = 'block';
        }
    }

    function openModal(id, percent, benefit, status) {
        document.getElementById("record_id").value = id;
        document.getElementById("percent").value = percent;
        document.getElementById("benefit").value = benefit;
        document.getElementById("status").value = status;
        document.getElementById("editModal").style.display = "flex";
    }

    function closeModal() {
        document.getElementById("editModal").style.display = "none";
    }

    <?php if ($updateSuccess): ?>
        // Show custom alert
        let alertBox = document.getElementById('customAlert');
        alertBox.style.display = 'block';
        setTimeout(() => {
            alertBox.style.display = 'none';
        }, 3000); // disappears after 3s
    <?php endif; ?>
</script>

<?php require_once './components/footer.php'; ?>