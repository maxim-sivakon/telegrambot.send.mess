<?php

/* telegram send messages with telegram bot */

class TKHelper
{
    const CREATE_LOGS = false;
    const TKDEBUG     = false;
    const BOT_DATA    = [
        "TEST_CHAT" => [
            "BOT_TOKEN" => "****",
            "CHAT_ID"   => "-100****",
        ],
    ];

    /**
     * @param  int  $userID
     * @return array
     */
    public static function getUser(int $userID = 0): array
    {
        /** @var array $arUser */
        $arUser = CUser::GetByID($userID)->Fetch();

        if ($arUser[ "UF_XING" ]) {
            $arUser[ "TELEGRAM" ] = str_starts_with($arUser[ "UF_XING" ],
                "@") ? $arUser[ "UF_XING" ] : "@".$arUser[ "UF_XING" ];
        }

        $arUser[ "LINK" ] = "https://****.ru/company/personal/user/".$arUser[ "ID" ]."/";
        $arUser[ "MARKDOWN_LINK" ] = "[Пользователь]({$arUser[ "LINK" ]})";
        $arUser[ "OUTPUT" ] = $arUser[ "TELEGRAM" ] ?? $arUser[ "MARKDOWN_LINK" ];

        return $arUser;
    }

    /**
     * @param  int  $dealID
     * @return array
     */
    public static function getDeal(int $dealID = 0): array
    {
        $dealInfo = [];

        $entityResult = \CCrmDeal::GetListEx(
            ['SOURCE_ID' => 'DESC'],
            [
                'ID'                => $dealID,
                'CHECK_PERMISSIONS' => 'N'
            ],
            false,
            false,
            [
                'ID',
                'TITLE',
                'STAGE_ID',
                'UF_CRM_1684843701867',
                'UF_CRM_1698783986',
                'UF_CRM_1457929450',
                'UF_CRM_1699496271',
                'UF_CRM_1683719576',
                'UF_CRM_1700817221464',
                'UF_CRM_1653046283',
                'UF_CRM_1701228716877',
                'UF_CRM_1445232100',
                'UF_CRM_1701241318',
                'CURRENCY_ID',
                'OPPORTUNITY'
            ]
        );

        while ($entity = $entityResult->fetch()) {
            $dealInfo = $entity;
        }

        return $dealInfo;
    }
}

class TKTelegramHelper
{

    /**
     * @param  string  $event
     * @param  array  $arDeal
     * @param  array|null  $arUser
     * @param  string|null  $multEvent
     * @return string
     */
    public static function createMessage(string $event, array $arDeal, ?array $arUser, ?string $multEvent): string
    {
        /** @var array $message */
        $message = [];

        if ($event == "MOVE_STAGE") {
            switch ($arDeal[ "STAGE_ID" ]) {
                case "C4:4":
                    if (empty($arDeal[ "dealInfo" ][ "UF_CRM_1698783986" ])) {
                        if (!empty($arDeal[ "dealInfo" ][ "UF_CRM_1684843701867" ])) {
                            $message[ 'text' ] .= "️⚠️ Сделка: [{$arDeal["ID"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/).\n Необходимо проверить поставщика ИНН: {$arDeal["INN"]}\n #проверитьИНН";
                        } else {
                            $message[ 'text' ] .= "️❗️ Сделка: [{$arDeal["ID"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/).\n Необходимо проверить поставщика ИНН: ИНН не указан. \n #требуетсяИНН";
                        }
                    }
                    break;
                case "C4:UC_NBQANO":
                    if (!empty($arDeal[ "dealInfo" ][ "UF_CRM_1698783986" ])) {
                        $message[ 'text' ] .= "✅️ Сделка: [{$arDeal["ID"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/).\n Поставщик проверен, результат проверки: {$arDeal["dealInfo"]["UF_CRM_1698783986"]}\n #естьРезультат";
                    } else {
                        $message[ 'text' ] .= "❗️️ Сделка: [{$arDeal["ID"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/).\n Поставщик проверен, результат проверки: не указано. \n #требуетсяРезультат";
                    }
                    break;
                case "C4:2":
                    if ($arDeal[ "dealInfo" ][ "UF_CRM_1701241318" ]) {
                        $add = "\n".$arDeal[ "dealInfo" ][ "UF_CRM_1701241318" ]."\n\n";
                    }
                    if ($arDeal[ "dealInfo" ][ "UF_CRM_1699496271" ] == 3119 || $arDeal[ "dealInfo" ][ "UF_CRM_1699496271" ] == "да" || $arDeal[ "arFields" ][ "UF_CRM_1699496271" ] == 3119 || $arDeal[ "arFields" ][ "UF_CRM_1699496271" ] == "да") {
                        $message[ 'text' ] .= "️️️🔥️ Сделка: [{$arDeal["ID"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/).\n Клиент сразу просит счет.\n Необходимо подготовить спецификацию.\n{$add}#подготовкаСпец";
                    } else {
                        $message[ 'text' ] .= "️️️❗️️ Сделка: [{$arDeal["ID"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/).\n Необходимо подготовить спецификацию.\n{$add}#подготовкаСпец";
                    }
                    break;
                case "C4:3":
                    if ($arDeal[ "dealInfo" ][ "UF_CRM_1701241318" ]) {
                        $add = "\n".$arDeal[ "dealInfo" ][ "UF_CRM_1701241318" ]."\n\n";
                    }
                    $message[ 'text' ] .= "️✅️ Сделка: [{$arDeal["ID"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/).\n Спецификация готова: {$arDeal["PRICE"]}\n{$add} #спецГотова";
                    break;
                case "C4:FINAL_INVOICE":
                    $message[ 'text' ] .= "🚗 Прошу рассчитать стоимость логистики по сделке [{$arDeal["ID"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/) [{$arDeal["TITLE"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/) \n #прошуРассчитать";
                    break;
                case "C4:1":
                    $message[ 'text' ] .= "✅ Рассчитана себестоимость перевозки по сделке [{$arDeal["ID"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/) [{$arDeal["TITLE"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/) \n #перевозкаРассчитана";
                    break;

                case "C6:FINAL_INVOICE":
                    if ($arDeal[ "dealInfo" ][ "UF_CRM_1653046283" ]) {
                        $add = "\n".$arDeal[ "dealInfo" ][ "UF_CRM_1653046283" ]."\n\n";
                    }
                    if ($arDeal[ "dealInfo" ][ "UF_CRM_1700817221464" ] == 3130 || $arDeal[ "dealInfo" ][ "UF_CRM_1700817221464" ] == "да" || $arDeal[ "arFields" ][ "UF_CRM_1700817221464" ] == 3130 || $arDeal[ "arFields" ][ "UF_CRM_1700817221464" ] == "да") {
                        $message[ 'text' ] .= "🇷🇺️ Сделка: [{$arDeal["ID"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/).\n ️🔥Требует реализации РФ\n\n {$arDeal["dealInfo"]["UF_CRM_1683719576"]}\n\n{$add} #требуетРеализацииРФ";
                    } else {
                        $message[ 'text' ] .= "🇷🇺️ Сделка: [{$arDeal["ID"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/).\n Требует реализации РФ\n\n {$arDeal["dealInfo"]["UF_CRM_1683719576"]}\n\n{$add} #требуетРеализацииРФ";
                    }
                    break;
                case "C6:UC_DEAGB6":
                    if ($arDeal[ "dealInfo" ][ "UF_CRM_1653046283" ]) {
                        $add = "\n".$arDeal[ "dealInfo" ][ "UF_CRM_1653046283" ]."\n\n";
                    }
                    if ($arDeal[ "dealInfo" ][ "UF_CRM_1700817221464" ] == 3130 || $arDeal[ "dealInfo" ][ "UF_CRM_1700817221464" ] == "да" || $arDeal[ "arFields" ][ "UF_CRM_1700817221464" ] == 3130 || $arDeal[ "arFields" ][ "UF_CRM_1700817221464" ] == "да") {
                        $message[ 'text' ] .= "🇰🇿️ Сделка: [{$arDeal["ID"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/).\n 🔥Необходимо подготовить СНТ\n\n {$arDeal["dealInfo"]["UF_CRM_1683719576"]}\n\n{$add} #необходимоПодготовитьСНТ";
                    } else {
                        $message[ 'text' ] .= "🇰🇿️ Сделка: [{$arDeal["ID"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/).\n Необходимо подготовить СНТ\n\n {$arDeal["dealInfo"]["UF_CRM_1683719576"]}\n\n #необходимоПодготовитьСНТ";
                    }

                    break;
                default:
                    break;
            }
        } else {
            if ($event == "UPDATE_FIELD") {
                switch ($arDeal[ "CHECK_FIELDS" ][ "UF_CRM_1698783986" ][ "CODE" ]) {
                    case "UF_CRM_1698783986":
                        if ($arDeal[ "CHECK_FIELDS" ][ "UF_CRM_1698783986" ][ "NEW" ] == "") {
                            $message[ 'text' ] .= "️⁉️️ Сделка: [{$arDeal["ID"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/)\n {$arUser["MARKDOWN_LINK"]} удален результат проверки поставщика.\nБыло: {$arDeal["CHECK_FIELDS"]["UF_CRM_1698783986"]["OLD"]}\n#удаленРезультат";
                        } elseif ($arDeal[ "CHECK_FIELDS" ][ "UF_CRM_1698783986" ][ "OLD" ] != "" && $arDeal[ "CHECK_FIELDS" ][ "UF_CRM_1698783986" ][ "OLD" ] != $arDeal[ "CHECK_FIELDS" ][ "UF_CRM_1698783986" ][ "NEW" ]) {
                            $message[ 'text' ] .= "️⁉️️ Сделка: [{$arDeal["ID"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/)\n {$arUser["MARKDOWN_LINK"]} изменён результат проверки поставщика.\nБыло: {$arDeal["CHECK_FIELDS"]["UF_CRM_1698783986"]["OLD"]}\nСтало: {$arDeal["CHECK_FIELDS"]["UF_CRM_1698783986"]["NEW"]}\n#изменёнРезультат";
                        }
                        break;
                }
            } else {
                if ($event == "CREATE_DEAL") {
                    $message[ 'text' ] .= "️+[{$arDeal["ID"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/) {$arUser[ "OUTPUT" ]} в работе до: {$arDeal["arFields"]["UF_CRM_1592278129"]}";
                } else {
                    if ($event == "MOVE_STAGE_MULTITUDE") {
                        switch ($multEvent) {
                            case "EVENT_1":
                                if (array_key_exists("UF_CRM_1653046283", $arDeal[ "arFields" ])) {
                                    $add = "\n".$arDeal[ "arFields" ][ "UF_CRM_1653046283" ]."\n\n";
                                } elseif (array_key_exists("UF_CRM_1653046283", $arDeal[ "dealInfo" ])) {
                                    $add = "\n".$arDeal[ "dealInfo" ][ "UF_CRM_1653046283" ]."\n\n";
                                }

                                if (array_key_exists("UF_CRM_1701228716877", $arDeal[ "arFields" ])) {
                                    $add .= "".$arDeal[ "arFields" ][ "UF_CRM_1701228716877" ]."\n\n";
                                } elseif (array_key_exists("UF_CRM_1701228716877", $arDeal[ "dealInfo" ])) {
                                    $add .= "".$arDeal[ "dealInfo" ][ "UF_CRM_1701228716877" ]."\n\n";
                                }

                                $message[ 'text' ] .= "✅ Сделка: [{$arDeal["ID"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/).\n Документы на отгрузку готовы\n{$add} #документыНаОтгрузкуГотовы";
                                break;
                            case "EVENT_2":
                            case "EVENT_4":
                                $message[ 'text' ] = $arDeal[ "arFields" ][ "UF_CRM_1701228716877" ] ?? $arDeal[ "dealInfo" ][ "UF_CRM_1701228716877" ];
                                break;
                            case "EVENT_3":
                                if (array_key_exists("UF_CRM_1653046283", $arDeal[ "arFields" ])) {
                                    $add = "\n".$arDeal[ "arFields" ][ "UF_CRM_1653046283" ]."\n\n";
                                } elseif (array_key_exists("UF_CRM_1653046283", $arDeal[ "dealInfo" ])) {
                                    $add = "\n".$arDeal[ "dealInfo" ][ "UF_CRM_1653046283" ]."\n\n";
                                }

                                if (array_key_exists("UF_CRM_1701228716877", $arDeal[ "arFields" ])) {
                                    $add .= "".$arDeal[ "arFields" ][ "UF_CRM_1701228716877" ]."\n\n";
                                } elseif (array_key_exists("UF_CRM_1701228716877", $arDeal[ "dealInfo" ])) {
                                    $add .= "".$arDeal[ "dealInfo" ][ "UF_CRM_1701228716877" ]."\n\n";
                                }

                                $message[ 'text' ] .= "✅ Сделка: [{$arDeal["ID"]}](https://****.ru/crm/deal/details/{$arDeal["ID"]}/).\n Готова реализация РФ\n{$add} #готоваРеализация";
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
        }

        return $message[ 'text' ] ?? false;
    }

    /**
     * @param  string  $chat_id
     * @param  string  $parse_mode
     * @param  string  $text
     * @param  string  $bot_token
     * @return array
     */
    public static function sendMessage(
        string $chat_id,
        string $parse_mode = "markdown",
        string $text,
        string $bot_token
    ) {
        $bot_token = \TKHelper::TKDEBUG ? \TKHelper::BOT_DATA[ "TEST_CHAT" ][ "BOT_TOKEN" ] : $bot_token;
        $chat_id = \TKHelper::TKDEBUG ? \TKHelper::BOT_DATA[ "TEST_CHAT" ][ "CHAT_ID" ] : $chat_id;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.telegram.org/bot'.$bot_token.'/sendMessage');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_POSTFIELDS, ["chat_id" => $chat_id, "parse_mode" => $parse_mode, "text" => $text]);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    public static function checkFields($arFields, bool $multitude, ?string $multEvent, ?string $stageID): bool
    {
        $result = false;

        if ($multitude) {
            switch ($multEvent) {
                case "EVENT_2":
                    if ($arFields[ "UF_CRM_1445232100" ] == "105" || $arFields[ "UF_CRM_1445232100" ] == "607" || $arFields[ "UF_CRM_1445232100" ] == "3048") {
                        $result = true;
                    }
                    break;
                default:
                    break;
            }
        } else {
            switch ($stageID) {
                case "STAGE_ID":
                    break;
                default:
                    break;
            }
        }


        return $result;
    }
}

class TelegramBotStage
{

    const BOT_DATA = [
        "C4:FINAL_INVOICE" => [
            "BOT_TOKEN"   => "****",
            "CHAT_ID"     => "-100****",
            "CHAT_NAME"   => "Расчет логистики",
            "FUNNEL_ID"   => 4,
            "FUNNEL_NAME" => "РАБОТА С ЗАЯВКОЙ",
            "STAGE_ID"    => "C4:FINAL_INVOICE",
            "STAGE_NAME"  => "на расчете логистики",
        ],
        "C4:1"             => [
            "BOT_TOKEN"   => "****",
            "CHAT_ID"     => "-100****",
            "CHAT_NAME"   => "Расчет логистики",
            "FUNNEL_ID"   => 4,
            "FUNNEL_NAME" => "РАБОТА С ЗАЯВКОЙ",
            "STAGE_ID"    => "C4:1",
            "STAGE_NAME"  => "логистика готова"
        ],
        "C4:4"             => [
            "BOT_TOKEN"   => "****",
            "CHAT_ID"     => "-100****",
            "CHAT_NAME"   => "Проверка контрагентов",
            "FUNNEL_ID"   => 4,
            "FUNNEL_NAME" => "РАБОТА С ЗАЯВКОЙ",
            "STAGE_ID"    => "C4:4",
            "STAGE_NAME"  => "проверка поставщика"

        ],
        "C4:UC_NBQANO"     => [
            "BOT_TOKEN"   => "****",
            "CHAT_ID"     => "-100****",
            "CHAT_NAME"   => "Проверка контрагентов",
            "FUNNEL_ID"   => 4,
            "FUNNEL_NAME" => "РАБОТА С ЗАЯВКОЙ",
            "STAGE_ID"    => "C4:UC_NBQANO",
            "STAGE_NAME"  => "поставщик проверен"
        ],
        "C4:2"             => [
            "BOT_TOKEN"   => "****",
            "CHAT_ID"     => "-100****",
            "CHAT_NAME"   => "Подготовка спецификации",
            "FUNNEL_ID"   => 4,
            "FUNNEL_NAME" => "РАБОТА С ЗАЯВКОЙ",
            "STAGE_ID"    => "C4:2",
            "STAGE_NAME"  => "подготовка спец"
        ],
        "C4:3"             => [
            "BOT_TOKEN"   => "****",
            "CHAT_ID"     => "-100****",
            "CHAT_NAME"   => "Подготовка спецификации",
            "FUNNEL_ID"   => 4,
            "FUNNEL_NAME" => "РАБОТА С ЗАЯВКОЙ",
            "STAGE_ID"    => "C4:3",
            "STAGE_NAME"  => "спец готова"
        ],
        "C6:FINAL_INVOICE" => [
            "BOT_TOKEN"   => "****",
            "CHAT_ID"     => "-100****",
            "CHAT_NAME"   => "Отгрузки бухгалтерии",
            "FUNNEL_ID"   => 4,
            "FUNNEL_NAME" => "Логистика",
            "STAGE_ID"    => "C6:FINAL_INVOICE",
            "STAGE_NAME"  => "Реализация РФ"
        ],
        "C6:UC_DEAGB6"     => [
            "BOT_TOKEN"   => "****",
            "CHAT_ID"     => "-100****",
            "CHAT_NAME"   => "Отгрузки бухгалтерии",
            "FUNNEL_ID"   => 4,
            "FUNNEL_NAME" => "Логистика",
            "STAGE_ID"    => "C6:UC_DEAGB6",
            "STAGE_NAME"  => "Реализация КЗ"
        ],
        "C6:UC_L2MDJF"     => [
            "MULTITUDE" => [
                "EVENT_3" => [
                    "BOT_TOKEN"   => "****",
                    "CHAT_ID"     => "-100****",
                    "CHAT_NAME"   => "Отгрузки бухгалтерии",
                    "FUNNEL_ID"   => 4,
                    "FUNNEL_NAME" => "Логистика",
                    "STAGE_ID"    => "C6:UC_L2MDJF",
                    "STAGE_NAME"  => "Ждем данные для СНТ",
                    "CHECK_HURD"  => false
                ]
            ]
        ],
        "C6:4"             => [
            "MULTITUDE" => [
                "EVENT_1" => [
                    "BOT_TOKEN"   => "****",
                    "CHAT_ID"     => "-100****",
                    "CHAT_NAME"   => "Отгрузки бухгалтерии",
                    "FUNNEL_ID"   => 4,
                    "FUNNEL_NAME" => "Логистика",
                    "STAGE_ID"    => "C6:4",
                    "STAGE_NAME"  => "РТУ готово, не отгружено",
                    "CHECK_HURD"  => false
                ],
                "EVENT_2" => [
                    "BOT_TOKEN"   => "****",
                    "CHAT_ID"     => "-100****",
                    "CHAT_NAME"   => "Документооборот",
                    "FUNNEL_ID"   => 4,
                    "FUNNEL_NAME" => "Логистика",
                    "STAGE_ID"    => "C6:4",
                    "STAGE_NAME"  => "РТУ готово, не отгружено",
                    "CHECK_HURD"  => true
                ]
            ]
        ]

    ];

    public static function startStage(&$arFields)
    {

        if (\Bitrix\Main\Loader::IncludeModule('crm')) {

            /** @var array $dealInfo */
            $dealInfo = \TKHelper::getDeal($arFields[ "ID" ]);

            if ($dealInfo[ "STAGE_ID" ] != $arFields[ "STAGE_ID" ]) {
                if (self::BOT_DATA[ $arFields[ "STAGE_ID" ] ]) {

                    $arDeal = [
                        "ID"           => $arFields[ "ID" ],
                        "TITLE"        => $dealInfo[ "TITLE" ],
                        "STAGE_ID"     => $arFields[ "STAGE_ID" ],
                        "INN"          => implode(', ', $dealInfo[ "UF_CRM_1684843701867" ]),
                        "PRICE"        => str_replace("&nbsp;", " ",
                            \CCrmCurrency::MoneyToString($dealInfo[ "OPPORTUNITY" ], $dealInfo[ "CURRENCY_ID" ])),
                        "dealInfo"     => $dealInfo,
                        "arFields"     => $arFields,
                        "CHECK_FIELDS" => [
                            "UF_CRM_1698783986" => [
                                "OLD"  => $dealInfo[ "UF_CRM_1698783986" ], "NEW" => $arFields[ "UF_CRM_1698783986" ],
                                "CODE" => "UF_CRM_1698783986"
                            ]
                        ]
                    ];

                    if (array_key_exists("MULTITUDE", self::BOT_DATA[ $arFields[ "STAGE_ID" ] ])) {

                        foreach (self::BOT_DATA[ $arFields[ "STAGE_ID" ] ][ "MULTITUDE" ] as $key => $stageItem) {

                            if (self::BOT_DATA[ $arFields[ "STAGE_ID" ] ][ "MULTITUDE" ][ $key ][ "CHECK_HURD" ] == false) {
                                \TKTelegramHelper::sendMessage($stageItem[ "CHAT_ID" ], "markdown",
                                    \TKTelegramHelper::createMessage("MOVE_STAGE_MULTITUDE", $arDeal, null, $key),
                                    $stageItem[ "BOT_TOKEN" ]);
                            } else {
                                if (\TKTelegramHelper::checkFields($arDeal[ "dealInfo" ], true, $key,
                                    $arDeal[ "STAGE_ID" ])) {
                                    \TKTelegramHelper::sendMessage($stageItem[ "CHAT_ID" ], "markdown",
                                        \TKTelegramHelper::createMessage("MOVE_STAGE_MULTITUDE", $arDeal, null, $key),
                                        $stageItem[ "BOT_TOKEN" ]);
                                }
                            }

                        }

                    } else {
                        \TKTelegramHelper::sendMessage(self::BOT_DATA[ $arDeal[ "STAGE_ID" ] ][ "CHAT_ID" ], "markdown",
                            \TKTelegramHelper::createMessage("MOVE_STAGE", $arDeal, null, null),
                            self::BOT_DATA[ $arDeal[ "STAGE_ID" ] ][ "BOT_TOKEN" ]);
                    }


                }
            }
        }

        return $arFields;
    }

}

addEventHandler('crm', 'OnBeforeCrmDealUpdate', ["TelegramBotStage", "startStage"]);

class TelegramBotUpdateField
{
    const BOT_DATA_FIELD = [
        "UF_CRM_1698783986" => [
            "BOT_TOKEN" => "****",
            "CHAT_ID"   => "-100****",
            "CHAT_NAME" => "Проверка контрагентов",
            "FIELD"     => "UF_CRM_1698783986",
            "EVENTS"    => [
                "EDIT", "DELETE"
            ],
        ]
    ];

    public static function startUpdate(&$arFields)
    {
        if (\Bitrix\Main\Loader::IncludeModule('crm')) {

            /** @var array $dealInfo */
            $dealInfo = \TKHelper::getDeal($arFields[ "ID" ]);

            if ($dealInfo[ "UF_CRM_1698783986" ] != $arFields[ "UF_CRM_1698783986" ] && array_key_exists('UF_CRM_1698783986',
                    $arFields)) {

                /** @var array $arUser */
                $arUser = \TKHelper::getUser($arFields[ "MODIFY_BY_ID" ]);

                $arDeal = [
                    "ID"           => $arFields[ "ID" ],
                    "TITLE"        => $dealInfo[ "TITLE" ],
                    "STAGE_ID"     => $arFields[ "STAGE_ID" ],
                    "INN"          => implode(', ', $dealInfo[ "UF_CRM_1684843701867" ]),
                    "PRICE"        => str_replace("&nbsp;", " ",
                        \CCrmCurrency::MoneyToString($dealInfo[ "OPPORTUNITY" ], $dealInfo[ "CURRENCY_ID" ])),
                    "dealInfo"     => $dealInfo,
                    "arFields"     => $arFields,
                    "CHECK_FIELDS" => [
                        "UF_CRM_1698783986" => [
                            "OLD"  => $dealInfo[ "UF_CRM_1698783986" ], "NEW" => $arFields[ "UF_CRM_1698783986" ],
                            "CODE" => "UF_CRM_1698783986"
                        ]
                    ]
                ];

                \TKTelegramHelper::sendMessage(self::BOT_DATA_FIELD[ "UF_CRM_1698783986" ][ "CHAT_ID" ], "markdown",
                    \TKTelegramHelper::createMessage("UPDATE_FIELD", $arDeal, $arUser, null),
                    self::BOT_DATA_FIELD[ "UF_CRM_1698783986" ][ "BOT_TOKEN" ]);
            }

        }

        return $arFields;
    }

}

addEventHandler('crm', 'OnBeforeCrmDealUpdate', ["TelegramBotUpdateField", "startUpdate"]);

class TelegramBotCreateDeal
{
    const BOT_DATA_CREATE = [
        "NEW_DEAL" => [
            "BOT_TOKEN" => "****",
            "CHAT_ID"   => "-100****",
            "CHAT_NAME" => "Подготовка спецификации",
            "TAG"       => "\n #новаяСделка",
            "MESSAGE"   => ""
        ],
    ];

    public static function startCreate(&$arFields)
    {

        if (\Bitrix\Main\Loader::IncludeModule('crm')) {
            if (array_key_exists('UF_CRM_1457929450', $arFields) && array_key_exists('UF_CRM_1592278129', $arFields)) {

                /** @var array $arUser */
                $arUser = \TKHelper::getUser($arFields[ "UF_CRM_1457929450" ]);

                /** @var array $dealInfo */
                $dealInfo = \TKHelper::getDeal($arFields[ "ID" ]);

                $arDeal = [
                    "ID"           => $arFields[ "ID" ],
                    "TITLE"        => $dealInfo[ "TITLE" ],
                    "STAGE_ID"     => $arFields[ "STAGE_ID" ],
                    "INN"          => implode(', ', $dealInfo[ "UF_CRM_1684843701867" ]),
                    "PRICE"        => str_replace("&nbsp;", " ",
                        \CCrmCurrency::MoneyToString($dealInfo[ "OPPORTUNITY" ], $dealInfo[ "CURRENCY_ID" ])),
                    "dealInfo"     => $dealInfo,
                    "arFields"     => $arFields,
                    "CHECK_FIELDS" => [
                        "UF_CRM_1698783986" => [
                            "OLD"  => $dealInfo[ "UF_CRM_1698783986" ], "NEW" => $arFields[ "UF_CRM_1698783986" ],
                            "CODE" => "UF_CRM_1698783986"
                        ]
                    ]
                ];

                $superResult = \TKTelegramHelper::sendMessage(self::BOT_DATA_CREATE[ "NEW_DEAL" ][ "CHAT_ID" ],
                    "markdown", \TKTelegramHelper::createMessage("CREATE_DEAL", $arDeal, $arUser, null),
                    self::BOT_DATA_CREATE[ "NEW_DEAL" ][ "BOT_TOKEN" ]);

            }
        }

        return $arFields;
    }

}

addEventHandler('crm', 'OnAfterCrmDealAdd', ["TelegramBotCreateDeal", "startCreate"]);
