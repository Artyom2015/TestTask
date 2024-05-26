<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

?>

<div class="GeoIP">
    <h1>GeoIP поиск</h1>
    <div class="GeoIP__search">
        <input type="text" title="Введите ваш IP" placeholder="Введите ваш IP" required>
        <div class="GeoIP__error-message" style="color: red;"></div>
        <button class="GeoIP__search-btn">Поиск</button>
    </div>
</div>
<div class="GeoIP__results"></div>

<style>
    .GeoIP {
        position: relative;
        border: 1px solid;
        width: 300px;
        height: 300px;
    }
    .GeoIP__search {
        position: absolute;
        top: 58%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .GeoIP__search-btn {
        margin-top: 30px;
        text-align: center;
        background: #70bb18;
        border: 0;
        color: #fff;
        font-size: 13px;
        position: relative;
        text-transform: uppercase;
        border-radius: 2px;
        line-height: 30px;
        height: 40px;
        padding: 5px 20px 5px 20px;
        vertical-align: middle;
        outline: 0;
        transition: width 5s linear;
    }

    .GeoIP__search-btn:hover, .GeoIP__search-btn:active {
        color: #fff;
    }

    .GeoIP__search-btn:hover {
        background: #7ec629;
    }

    .GeoIP__results {
        margin-top: 20px;
        padding: 10px;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 4px;
        width: fit-content;
        height: fit-content;
    }
</style>

<script>
    addEventListener("DOMContentLoaded", () => {

        const btn = document.querySelector('.GeoIP__search-btn');
        const input = document.querySelector('.GeoIP__search input[type="text"]');
        const errorMessage = document.querySelector('.GeoIP__error-message');

        // Функция для создания красивого вывода данных
        function displayResults(data) {
            let resultsContainer = document.querySelector('.GeoIP__results');
            resultsContainer.innerHTML = ''; // Очистка предыдущих результатов

            // Проверка, что данные не пустые и нет ошибки
            if (data && !data.error && data.msg) {
                // Создание и заполнение элементов для каждого свойства объекта msg
                Object.keys(data.msg).forEach(function(key) {
                    let value = data.msg[key];
                    let element = document.createElement('div');
                    element.textContent = key.replace('UF_', '') + ': ' + value; // Убираем префикс 'UF_'
                    resultsContainer.appendChild(element);
                });
            } else {
                // Если данных нет или есть ошибка, выводим сообщение
                resultsContainer.textContent = 'Информация по данному IP не найдена или произошла ошибка.';
            }
        }

        btn.addEventListener('click', (event) => {
            console.log(input.value);
            // event.preventDefault();
            // Очистка предыдущего сообщения об ошибке
            errorMessage.textContent = '';

            // Проверка на пустое значение поля ввода
            if (!input.value.trim()) {
                errorMessage.textContent = 'Пожалуйста, введите IP-адрес.';
                return; // Прекращение выполнения функции, если поле пустое
            }
            if (!input.value.match(/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/)) {
                errorMessage.textContent = 'Пожалуйста, введите корректный IP-адрес.';
            } else {
                errorMessage.textContent = '';
                BX.ajax({
                    url: '/ajax/geoIP_search.php',
                    data: {
                        IP: input.value
                    },
                    method: 'POST',
                    dataType: 'json',
                    timeout: 30,
                    async: true,
                    processData: true,
                    scriptsRunFirst: true,
                    emulateOnload: true,
                    start: true,
                    cache: false,
                    onsuccess: function (response) {
                        console.log(response)
                        displayResults(response); // Вызов функции для отображения результатов
                    },
                    onfailure: function () {
                        filterCountLoading = false;
                    },
                });
            }
        });

    });
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>