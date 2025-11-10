<?php
$pageTitle = "خرید API";
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
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f0f2f5;
        margin: 0;
        padding: 0;
    }

    /* Table */
    table {
        border-collapse: collapse;
        width: 100%;
        margin-top: 20px;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    th,
    td {
        padding: 12px 15px;
        text-align: center;
    }

    th {
        background: #4f46e5;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    td {
        border-bottom: 1px solid #eee;
    }

    tr:last-child td {
        border-bottom: none;
    }

    /* Buttons */
    button {
        padding: 6px 12px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-edit {
        background: #4f46e5;
        color: #fff;
    }

    .btn-edit:hover {
        background: #3730a3;
    }

    .btn-cancel {
        background: #9ca3af;
        color: #fff;
    }

    .btn-save {
        background: #10b981;
        color: #fff;
    }

    .btn-save:hover {
        background: #059669;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .modal-content {
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        width: 420px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        animation: fadeIn 0.3s ease;
    }

    .modal-header {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 20px;
        text-align: center;
        color: #111827;
    }

    .form-group {
        margin-bottom: 16px;
        text-align: right;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        outline: none;
        transition: border 0.2s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    }

    /* Success Alert */
    #successAlert {
        display: none;
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #10b981;
        color: #fff;
        padding: 15px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        font-weight: bold;
        z-index: 2000;
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.5s ease;
    }

    #successAlert.show {
        display: block;
        opacity: 1;
        transform: translateY(0);
    }

    @keyframes fadeIn {
        from {
            opacity: 0
        }

        to {
            opacity: 1
        }
    }
</style>

<!-- Success Alert -->
<div id="successAlert">تنظیمات با موفقیت بروزرسانی شد!</div>

<div class="max-w-3xl mx-auto mt-32 px-6">
    <table id="hussainTable">
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
                <tr>
                    <td>
                        <button class="px-3 py-2 bg-teal-700 text-white" onclick="loadHussainPartData()">بروزسانی اطلاعات</button>
                    </td>
                    <td colspan="3">
                        <p id="message" class="text-gray-800">Hello</p>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

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
            <div style="text-align:center; margin-top: 20px;">
                <button type="button" class="btn-cancel" onclick="closeModal()">لغو</button>
                <button type="submit" class="btn-save">ثبت</button>
            </div>
        </form>
    </div>
</div>

<script>
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

    // Show success alert after update
    <?php if ($updateSuccess): ?>
        let alertBox = document.getElementById('successAlert');
        alertBox.classList.add('show');
        setTimeout(() => {
            alertBox.classList.remove('show');
        }, 3000); // hide after 3 seconds
    <?php endif; ?>

    function loadHussainPartData() {
        const message = document.getElementById("message");
        message.classList.remove("hidden");
        message.innerHTML = "در حال پراسس، لطفا منتظر باشید.";
        axios.get("../../test.php").then((response) => {
            const message = document.getElementById("message");
            message.innerHTML = response.data;

        }).catch((e) => {
            console.log(e);
        });
    }
</script>

<?php require_once './components/footer.php'; ?>
