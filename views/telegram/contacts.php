<?php
$pageTitle = "لیست مخاطبین پیام خودکار";
$iconUrl = 'telegram.svg';
require_once './components/header.php';
require_once '../../app/controller/telegram/TelegramController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
?>
<style>
    .modal_container2 {
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 100000000000000000;
        flex-direction: column;
        align-items: center;
        padding-block: 10px;
    }
</style>
<section class="p-5 max-w-5xl drop-shadow-lg mx-auto rounded-md my-5">
    <div class="flex justify-between">
        <h2 class="text-xl font-bold ">مخاطبین</h2>
        <input onkeyup="searchContact(this.value)" class="px-3 py-2 border w-1/2" type="text" placeholder="جستجوی مخاطبین ....">
        <div class="flex items-center gap-3">
            <img title="بارگیری مخاطبین جدید" class="cursor-pointer" onclick="toggleModalDisplay2()" src="./assets/img/reload.svg" alt="reload icon">
        </div>
    </div>
    <table class="w-full mt-3">
        <thead>
            <tr class="bg-gray-800">
                <th class="text-right p-3 text-white text-sm">#</th>
                <th class="text-right p-3 text-white text-sm">اسم</th>
                <th class="text-right p-3 text-white text-sm">نام کاربری</th>
                <th class="text-right p-3 text-white text-sm">
                    <img src="./assets/img/setting.svg" />
                </th>
            </tr>
        </thead>
        <tbody id="existingContactsContainer">
            <!-- Partial contacts will be appended here -->
        </tbody>
    </table>
    <div id="pagination"></div>
</section>

<!-- MODAL TO ADD NEW PART NUMBER  -->
<div id="new_contact_container" class="modal_container2" style=" display: none;">
    <div class="bg-white rounded-lg w-1/2 p-5 border-b overflow-auto">
        <div class="flex justify-between">
            <h2 class="font-bold text-xl">مخاطبین جدید</h2>
            <img onclick="toggleModalDisplay2()" class="cursor-pointer" src="./assets/img/close.svg" alt="close icon">
        </div>
        <div class="py-5">
            <table class="w-full mt-3">
                <thead>
                    <tr class="bg-gray-800">
                        <th class="text-right text-white py-2 px-3 text-sm">#</th>
                        <th class="text-right text-white py-2 px-3 text-sm">اسم</th>
                        <th class="text-right text-white py-2 px-3 text-sm">نام کاربری</th>
                        <th class="text-right text-white py-2 px-3 text-sm">
                            <img class="w-6 h-6" src="./assets/img/addAll.svg" alt="add to database icon" onclick="addAllContacts()">
                        </th>
                    </tr>
                </thead>
                <tbody id="newContacts">
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const existingContacts = <?= json_encode(array_column($contacts, 'chat_id')); ?>;
    const existingContactsContainer = document.getElementById('existingContactsContainer');
    const contactApi = "../../app/api/telegram/ContactsApi.php";
    let NewContacts = [];

    function toggleModalDisplay2() {
        if (new_contact_container.style.display === 'none') {
            connect();
            new_contact_container.style.display = 'flex';
        } else {
            new_contact_container.style.display = 'none';
        }

    }

    function connect() {
        var params = new URLSearchParams();
        params.append('getBlockedContacts', 'getBlockedContacts');
        const container = document.getElementById('newContacts');
        container.innerHTML = `
                        <tr class="even:bg-gray-200">
                            <td class="py-5 px-3 text-sm text-center" colspan="4">
                                <img class="w-12 h-12 mx-auto" src="./assets/img/loading.png" />
                                <br />
                                <p class="text-sm">لطفا صبور باشید</p>
                            </td>
                        </tr>`;

        axios
            .post("../../app/api/telegram/ContactsApi.php", params)
            .then(function(response) {
                const contacts = response.data;
                let template = ``;
                let counter = 1;
                let newContactFound = false;

                for (const contact of contacts) {
                    if ((!existingContacts.includes(contact.id))) {
                        NewContacts.push(contact);
                        template += `
                    <tr class="even:bg-gray-200 odd:bg-white" id=${contact.chat_id}>
                        <td class="py-2 px-3 text-sm">${counter}</td>
                        <td class="py-2 px-3 text-sm">${contact.name}</td>
                        <td class="py-2 px-3 text-sm">${contact.username ?? ''}</td>
                        <td class="py-2 px-3 text-sm cursor-pointer" 
                            onclick="addContact(
                                '${contact.name}',
                                '${contact.username ?? ''}',
                                '${contact.chat_id}',
                                'rezaei.jpeg'
                            )">
                            <img src="./assets/img/add.svg" alt="plus icon">
                        </td>
                    </tr>`;
                        counter++;
                        newContactFound = true;
                    }
                }

                if (!newContactFound) {
                    template = `
                <tr class="even:bg-gray-200">
                    <td class="py-5 px-3 text-sm text-center" colspan="4">
                        <p class="text-sm">هیچ مخاطب جدیدی یافت نشد</p>
                    </td>
                </tr>`;
                }

                container.innerHTML = template;
            })
            .catch(function(error) {
                console.log(error);
            });

    }

    function addContact(name, username, chat_id, profile) {
        var params = new URLSearchParams();
        params.append('addContact', 'addContact');
        params.append('name', name);
        params.append('username', username);
        params.append('chat_id', chat_id);
        params.append('profile', profile);

        axios.post(contactApi, params)
            .then(function(response) {
                const data = response.data;
                if (data == 'exist') {
                    alert('مخاطب از قبل در سیستم موجود است.');
                } else if (data == true) {
                    alert('مخاطب موفقانه اضافه شد.');
                    document.getElementById(chat_id).remove();
                } else {
                    alert('مشکلی  در هنگام اضافه کردن مخاطب رخ داده است.');
                }
            })
            .catch(function(error) {
                console.log(error);
            });

    }

    function addAllContacts() {

        var params = new URLSearchParams();
        params.append('addAllContact', 'addAllContact');
        params.append('contacts', JSON.stringify(NewContacts));

        if (NewContacts.length > 0) {
            axios.post(contactApi, params)
                .then(function(response) {
                    const data = response.data;
                    if (data == 'exist') {
                        alert('مخاطب از قبل در سیستم موجود است.');
                    } else if (data == true) {
                        window.location.reload();
                    } else {
                        alert('مشکلی  در هنگام اضافه کردن مخاطب رخ داده است.');
                    }
                })
                .catch(function(error) {
                    console.log(error);
                });
        }
    }

    function getPartialContacts(page = 1) {
        var params = new URLSearchParams();
        params.append('getPartialContacts', 'getPartialContacts');
        params.append('page', page);

        axios
            .post(contactApi, params)
            .then(function(response) {
                const contacts = response.data;
                if (contacts.length > 0) {
                    let template = ``;
                    let counter = null;

                    if (page == 1) {
                        counter = 1;
                    } else {
                        counter = (Number(page) - 1) * 50 + 1;
                    }
                    for (contact of contacts) {
                        template += `
                        <tr id="parent-${contact.id}" class="even:bg-gray-200 odd:bg-white">
                            <td class="py-2 px-3 text-sm">${counter}</td>
                            <td class="py-2 px-3 text-sm">${contact.name}</td>
                            <td class="py-2 px-3 text-sm">${contact.username}</td>
                            <td class="py-2 px-3 text-sm cursor-pointer" 
                                onclick="deleteContact('${contact.id}')">
                                <img src="./assets/img/del.svg" alt="plus icon">
                            </td>
                        </tr>`;
                        counter++;
                    }
                    existingContactsContainer.innerHTML = template;
                }
            })
            .catch(function(error) {
                console.log(error);
            });
    }

    function getContactsCount() {
        var params = new URLSearchParams();
        params.append('getContactsCount', 'getContactsCount');
        axios
            .post(contactApi, params)
            .then(function(response) {
                const totalItems = response.data;
                const itemsPerPage = 50;
                const totalPages = Math.ceil(totalItems / itemsPerPage);

                createPagination(totalPages);
            })
            .catch(function(error) {
                console.log(error);
            });
    }

    function createPagination(totalPages, currentPage = 1) {
        const paginationElement = document.getElementById('pagination');
        paginationElement.innerHTML = ''; // Clear any existing pagination

        const paginationList = document.createElement('ul');
        paginationList.className = 'flex text-white p-2 rounded-md justify-center items-center';

        // Add first page link
        if (currentPage > 1) {
            const firstPage = createPaginationItem(totalPages, 1, '>');
            paginationList.appendChild(firstPage);
        }

        // Add previous page link
        if (currentPage > 1) {
            const prevPage = createPaginationItem(totalPages, currentPage - 1, '<');
            paginationList.appendChild(prevPage);
        }

        // Add page links
        const maxVisiblePages = 5; // Number of page links to show
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageItem = createPaginationItem(totalPages, i, i, currentPage === i);
            paginationList.appendChild(pageItem);
        }

        // Add next page link
        if (currentPage < totalPages) {
            const nextPage = createPaginationItem(totalPages, currentPage + 1, '>');
            paginationList.appendChild(nextPage);
        }

        // Add last page link
        if (currentPage < totalPages) {
            const lastPage = createPaginationItem(totalPages, totalPages, '<');
            paginationList.appendChild(lastPage);
        }

        paginationElement.appendChild(paginationList);
    }

    function createPaginationItem(totalPages, page, text, isActive = false) {
        const li = document.createElement('li');
        li.className = `bg-gray-600 text-white m-1 rounded w-8 h-8 ${isActive ? 'bg-gray-800' : ''}`;

        const a = document.createElement('a');
        a.className = 'px-2 py-1 block w-full h-full text-center';
        a.href = '#';
        a.innerText = text;
        a.addEventListener('click', function(event) {
            event.preventDefault();
            createPagination(totalPages, page);
            getPartialContacts(page);
        });

        li.appendChild(a);
        return li;
    }

    function deleteContact(id) {
        const parent = document.getElementById(`parent-${id}`);
        var params = new URLSearchParams();
        params.append('deleteContact', 'deleteContact');
        params.append('id', id);

        axios.post(contactApi, params)
            .then(function(response) {
                const data = response.data;
                if (data == true) {
                    if (parent) {
                        parent.remove();
                    }
                }
            })
            .catch(function(error) {
                console.log(error);
            });
    }

    function searchContact(pattern) {
        if (pattern.length > 3) {
            var params = new URLSearchParams();
            params.append('searchContact', 'searchContact');
            params.append('pattern', pattern);
            existingContactsContainer.innerHTML = `
                        <tr class="even:bg-gray-200 odd:bg-white">
                            <td colspan="4" class="p-3 text-center text-red-600 text-sm">مورد مشابهی پیدا نشد.</td>
                        </tr>`;
            axios
                .post(contactApi, params)
                .then(function(response) {
                    const contacts = response.data;
                    console.log(contacts);
                    if (contacts.length > 0) {
                        let template = ``;
                        existingContactsContainer.innerHTML = template;
                        let counter = 1;
                        for (contact of contacts) {
                            template += `
                        <tr class="even:bg-gray-200 odd:bg-white">
                            <td class="py-2 px-3 text-sm">${counter}</td>
                            <td class="py-2 px-3 text-sm">${contact.name}</td>
                            <td class="py-2 px-3 text-sm">${contact.username}</td>
                            <td class="py-2 px-3 text-sm cursor-pointer" 
                                onclick="deleteContact('${contact.id}')">
                                <img src="./assets/img/del.svg" alt="plus icon">
                            </td>
                        </tr>`;
                            counter++;
                        }
                        existingContactsContainer.innerHTML = template;
                    } else {
                        existingContactsContainer.innerHTML = `
                        <tr class="even:bg-gray-200 odd:bg-white">
                            <td colspan="4" class="p-3 text-center text-red-600 text-sm">مورد مشابهی پیدا نشد.</td>
                        </tr>`;
                    }
                })
                .catch(function(error) {
                    console.log(error);
                });
        }
        if (pattern.length == 0) {
            getPartialContacts();
        }
    }

    getPartialContacts();
    getContactsCount();
</script>
<?php
require_once './components/footer.php';
?>