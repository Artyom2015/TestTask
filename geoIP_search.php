<?include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php"); 

// Подключаем модуль для работы с highloadblock блоками
use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;

Loader::includeModule("highloadblock");

// Функция для генерации сообщения
function genMsg($isErr, $msg){
	return json_encode([ 'error' => $isErr, 'msg' => $msg]);
}

// Функция для добавления записи в highloadblock
function addHLBlock($arrGeoIP) {
    $hlbl = 4; // Указываем ID нашего highloadblock блока к которому будет делать запросы.
    $hlblock = HL\HighloadBlockTable::getById($hlbl)->fetch(); 

    $entity = HL\HighloadBlockTable::compileEntity($hlblock); 
    $entity_data_class = $entity->getDataClass(); 

    // Массив полей для добавления
    $data = [
        "UF_CITY_NAME" => $arrGeoIP["city_name"],
        "UF_COUNTRY_CODE" => $arrGeoIP["country_code"],
        "UF_COUNTRY_NAME" => $arrGeoIP["country_name"],
        "UF_IP" => $arrGeoIP["ip"],
        "UF_LATITUDE" => $arrGeoIP["latitude"],
        "UF_LONGITUDE" => $arrGeoIP["longitude"],
        "UF_REGION_NAME" => $arrGeoIP["region_name"],
        "UF_TIME_ZONE" => $arrGeoIP["time_zone"],
    ];

    $result = $entity_data_class::add($data);
    return $result;
}

// Функция для получения записи из highloadblock по IP
function getHLBlock($data) {
    // Делаем выборку из highloadblock блока метод getlist
    $hlbl = 4; // Указываем ID нашего highloadblock блока к которому будем делать запросы.
    $hlblock = HL\HighloadBlockTable::getById($hlbl)->fetch(); 
    
    $entity = HL\HighloadBlockTable::compileEntity($hlblock); 
    $entity_data_class = $entity->getDataClass(); 
    
    $rsData = $entity_data_class::getList([
       "select" => [
            "*",
       ],
       "filter" => [
            "UF_IP" => $data,
       ]
    ])->Fetch();
    
    return $rsData;
}

// Проверяем, переданны ли данные через POST запрос
if (!$_POST['IP']) {
    echo genMsg(
        true,
        "Нет данных!"
    );
}

$data = $_POST['IP']; // Записываем пришедшие данные в переменную

$result = getHLBlock($data);

if (!$result) {
    $api_key = '6795574098C5CB5AC0E994CCD41B406D'; // Заменить на ваш API ключ
    $url = "https://api.ip2location.io/?key={$api_key}&ip={$data}";

    $response = file_get_contents($url);
    $result = json_decode($response, true);

    if ($result['city_name'] && $result['city_name'] !== '-') { // Проверка на пустоту и знак "-"
        addHLBlock($result);

        // Повторно получаем данные из highloadblock после добавления записи
        $result = getHLBlock($data);
    
        echo genMsg(
            false,
            $result
        );
    } else {
        echo genMsg(
            true,
            "Извините, по данному IP ничего не найдено"
        );
    }
} else {
    echo genMsg(
        false,
        $result
    );
}

?>