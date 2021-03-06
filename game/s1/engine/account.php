<?php

/*
 * Develop by Phumin Chanthalert from Thailand
 * Facebook : http://fb.com/phoomin2012
 * Tel. : 091-8585234 (Thai mobile)
 * Copy Rigth © Phumin Chanthalert.
 */

class Account {

    public $start_gold = 100;
    public $plus_time = 86400 * 5;
    public $resBonus_time = 86400 * 5;
    public $cropBonus_time = 86400 * 5;
    public $prestige_data = [
        "0" => [
            "bronze" => 0,
            "silver" => 0,
            "gold" => 0
        ],
        "25" => [
            "bronze" => 1,
            "silver" => 0,
            "gold" => 0
        ],
        "50" => [
            "bronze" => 2,
            "silver" => 0,
            "gold" => 0
        ],
        "100" => [
            "bronze" => 3,
            "silver" => 0,
            "gold" => 0
        ],
        "200" => [
            "bronze" => 3,
            "silver" => 1,
            "gold" => 0
        ],
        "300" => [
            "bronze" => 3,
            "silver" => 2,
            "gold" => 0
        ],
        "400" => [
            "bronze" => 0,
            "silver" => 3,
            "gold" => 0
        ],
        "500" => [
            "bronze" => 0,
            "silver" => 3,
            "gold" => 1
        ],
        "750" => [
            "bronze" => 0,
            "silver" => 3,
            "gold" => 2
        ],
        "1000" => [
            "bronze" => 0,
            "silver" => 0,
            "gold" => 3
        ],
        "2000" => [
            "bronzeBadge" => 1,
            "bronze" => 0,
            "silver" => 0,
            "gold" => 0
        ],
        "5000" => [
            "silverBadge" => 1,
            "bronze" => 0,
            "silver" => 0,
            "gold" => 0
        ],
        "10000" => [
            "goldBadge" => 1,
            "bronze" => 0,
            "silver" => 0,
            "gold" => 0
        ]
    ];

    public function FPLogin() {
        global $engine;

        $q = query("SELECT * FROM `" . $engine->server->prefix . "first_play` WHERE `email`=?", array($_SESSION['mellon_email']))->fetch();
        $id = $q['id'];

        $_SESSION[$engine->server->prefix . 'first_play'] = true;

        return $id;
    }

    public function Login($username) {
        global $engine;
        $q = query("SELECT * FROM `" . $engine->server->prefix . "user` WHERE `username`=?;", array($username));
        $n = $q->rowCount($q);
        $us = $q->fetch();
        if ($n == 1) {
            $q2 = query("SELECT * FROM `global_user` WHERE `username`=?;", array($us['username']));
            $u = $q2->fetch(PDO::FETCH_ASSOC);
            $u2 = $q->fetch(PDO::FETCH_ASSOC);
            array_push($u2, $u);
            $engine->session->data = (object) $us;
            $_SESSION[$engine->server->prefix . 'uid'] = $u['uid'];
            $_SESSION[$engine->server->prefix . 'username'] = $u['username'];
            $_SESSION[$engine->server->prefix . 'avatar'] = $u['avatar'];
            $_SESSION[$engine->server->prefix . 'gold'] = $u['gold'];
            $_SESSION[$engine->server->prefix . 'tribe'] = $u['tribe'];
            $_SESSION[$engine->server->prefix . 'tutorial'] = $u['tutorial'];
            return true;
        } else {
            return false;
        }
    }

    public function edit($field, $value, $user = null) {
        global $engine;
        ($user === null ) ? $user = $_SESSION[$engine->server->prefix . 'uid'] : '';
        query("UPDATE `" . $engine->server->prefix . "user` SET `" . $field . "`=? WHERE `uid`=?;", array($value, $user));

        $q = query("SELECT * FROM `" . $engine->server->prefix . "user` WHERE `uid`=?;", array($user));
        $n = $q->rowCount($q);
        $us = $q->fetch(PDO::FETCH_ASSOC);
        if ($n == 1) {
            $q2 = query("SELECT * FROM `global_user` WHERE `uid`=?;", array($us['username']));
            $u = $q2->fetch(PDO::FETCH_ASSOC);
            array_push($us, $u);
            $engine->session->data = (object) $us;
        }
    }

    public function getByVillage($wid, $field = null) {
        global $engine;
        $v = query("SELECT * FROM `" . $engine->server->prefix . "village` WHERE `wid`=?;", array($wid))->fetch(PDO::FETCH_ASSOC);
        return $this->getById($v['owner'], $field);
    }

    public function getById($uid, $field = null) {
        global $engine;
        $p = query("SELECT * FROM `" . $engine->server->prefix . "user` WHERE `uid`=?;", array($uid))->fetch(PDO::FETCH_ASSOC);

        if ($field === null) {
            return $p;
        } else {
            return $p[$field];
        }
    }

    public function getProfile($uid = null, $head = true) {
        global $engine;
        ($uid === null ) ? $uid = $_SESSION[$engine->server->prefix . 'uid'] : '';
        $u = query("SELECT * FROM `" . $engine->server->prefix . "user` WHERE `uid`=?;", [$uid])->fetch(PDO::FETCH_ASSOC);
        $r = [
            "name" => "PlayerProfile:" . $uid,
            "data" => [
                "description" => $u['desc'],
            ]
        ];
        return $r;
    }

    public function getAjax($id = null) {
        global $engine;
        ($id === null ) ? $id = $_SESSION[$engine->server->prefix . 'uid'] : '';

        $p = query("SELECT * FROM `" . $engine->server->prefix . "user` WHERE `uid`=?", array($id))->fetch(PDO::FETCH_ASSOC);
        $prestige = $this->getPrestige(null, true);
        $k = $engine->kingdom->getData($p['kingdom']);
        $r = [
            'name' => 'Player:' . $id,
            'data' => [
                'playerId' => $id,
                'name' => $p['username'] == null ? "" : $p['username'],
                'tribeId' => $p['tribe'],
                'kingdomId' => $p['kingdom'],
                'kingdomRole' => $engine->kingdom->getRole($p['kingdom'], $id) != 0 ? 1 : 0,
                'kingdomTag' => $k['tag'],
                'kingId' => $k['king'],
                'kingstatus' => $k['king'] == $p['uid'] ? '1' : '0',
                'isKing' => ($engine->kingdom->getRole($p['kingdom'], $id) == 1) ? true : false,
                'isActivated' => '1',
                'isInstant' => '0',
                'isBannedFromMessaging' => false,
                'isPunished' => false,
                'villages' => $engine->village->getAll($p['uid'], false),
                'population' => $engine->village->getAllPop($p['uid']),
                'level' => 0,
                'stars' => $prestige['prestigeStars'],
                'prestige' => $prestige['globalPrestige'],
                'nextLevelPrestige' => $prestige['nextLevelGlobalPrestige'],
                'hasNoobProtection' => false,
                'filterInformation' => false,
                'uiLimitations' => '-1',
                'uiStatus' => '-1',
                'gold' => $p['gold'],
                'silver' => $p['silver'],
                'taxRate' => '0',
                'coronationDuration' => 0,
                'brewCelebration' => '0',
                'hintStatus' => '1',
                'spawnedOnMap' => $p['spawn'],
                'signupTime' => $p['spawn'],
                'productionBonusTime' => $p['resBonus'],
                'cropProductionBonusTime' => $p['cropBonus'],
                'premiumFeatureAutoExtendFlags' => $p['autoExtend'],
                'plusAccountTime' => $p['plus'],
                'dailyQuestsExchanged' => '0',
                'nextDailyQuestTime' => '0',
                'deletionTime' => '0',
                'lastPaymentTime' => '0',
                'limitation' => '0',
                'limitationFlags' => '0',
                'limitedPremiumFeatureFlags' => ($p['master'] == "0") ? 1 : (($p['master'] == "1") ? 2 : (($p['master'] == "2") ? 6 : 14)),
                'bannedFromMessaging' => '0',
                'questVersion' => '2',
                'avatarIdentifier' => '1',
                'active' => "1",
            ],
        ];

        if ($p['quest'] == 0) {
            
        }
        if ($p['tutorial'] < 256) {
            $r['data']['uiLimitations'] = 0;
            $r['data']['uiStatus'] = 0;
            $r['data']['hintStatus'] = 0;
        }
        if ($p['tutorial'] >= 9) {
            $r['data']['uiStatus'] = '75497520';
        }
        if ($p['tutorial'] >= 14) {
            $r['data']['uiStatus'] = '75497776';
        }
        if ($p['tutorial'] >= 14) {
            $r['data']['uiStatus'] = '76489015';
        }
        if ($p['tutorial'] >= 19) {
            $r['data']['uiStatus'] = '76493111';
        }
        if ($p['tutorial'] >= 22) {
            $r['data']['uiStatus'] = '2147483647';
        }
        if ($p['tutorial'] >= 256) {
            $r['data']['uiStatus'] = '-1';
            $r['data']['uiLimitations'] = '-1';
        }

        return $r;
    }

    public function getCPproduce($uid) {
        global $engine;

        $vs = query("SELECT `wid`,`cp` FROM `{$engine->server->prefix}village` WHERE `owner`=?", [$uid])->fetchAll(PDO::FETCH_ASSOC);
        $cpp = 0;
        foreach ($vs as $v) {
            $cpp += $engine->village->getProc($v['wid'], 5);
        }
        return $cpp;
    }

    public function getPrestige($uid = null, $onlystar = false) {
        global $engine;

        $uid === null ? $uid = $_SESSION[$engine->server->prefix . 'uid'] : 0;
        $email = $this->getById($uid, 'email');
        $gu = query("SELECT * FROM `global_user` WHERE `email`=?;", [$email])->fetch(PDO::FETCH_ASSOC);
        $prestige = $gu['prestige'];
        $next_prestige = 0;
        foreach ($this->prestige_data as $pr => $s) {
            if ($prestige >= $pr){
                $star = $s;
            }else{
                $next_prestige = $pr;
                break;
            }
        }

        $r = array(
            'gameworldPrestige' => 6,
            'globalPrestige' => $prestige,
            'nextLevelGlobalPrestige' => $next_prestige,
            'remainingDays' => 5,
            'weekPrestigeAmount' => 7,
            'prestigeStars' => $star,
            'rankings' => array(
                array(
                    'achievedValue' => 1266,
                    'avatarIdentifier' => "",
                    'conditionType' => "9",
                    'controlValue' => "1599",
                    'croppedValue' => 1,
                    'finalValue' => 333,
                    'fulfilled' => "1",
                    'id' => "20735",
                    'threshold' => 1,
                    'type' => "ranking",
                )
            ),
            'top10rankings' => array(
                array(
                    'prestige' => 0,
                    'rank' => 449,
                    'type' => "top10Attacker",
                )
            ),
            'conditions' => array(
                array(
                    'achievedValue' => "10",
                    'avatarIdentifier' => "2000",
                    'conditionType' => "15",
                    'controlValue' => "0",
                    'croppedValue' => 3,
                    'finalValue' => 10,
                    'fulfilled' => "1",
                    'id' => "1",
                    'threshold' => 3,
                    'type' => 15
                )
            ),
        );
        if ($onlystar)
            return $star;
        else
            return $r;
    }

    public function buyPremium($type, $price, $lifttime = false) {
        global $engine;

        if ($type == 0) {
            if ($engine->session->data->plus != -1) {
                if ($engine->session->data->gold >= $price) {
                    $engine->account->edit("gold", $engine->session->data->gold - $price);
                    $duration = ($lifttime) ? -1 : (($engine->session->data->plus != 0) ? $engine->session->data->plus + $this->plus_time : time() + $this->plus_time);
                    query("UPDATE `{$engine->server->prefix}user` SET `plus`=? WHERE `uid`=?;", [$duration, $engine->session->data->uid]);
                }
            }
        } elseif ($type == 1) {
            if ($engine->session->data->resBonus != -1) {
                if ($engine->session->data->gold >= $price) {
                    $engine->account->edit("gold", $engine->session->data->gold - $price);
                    $duration = ($lifttime) ? -1 : (($engine->session->data->resBonus != 0) ? $engine->session->data->resBonus + $this->resBonus_time : time() + $this->resBonus_time);
                    query("UPDATE `{$engine->server->prefix}user` SET `resBonus`=? WHERE `uid`=?;", [$duration, $engine->session->data->uid]);
                }
            }
        } elseif ($type == 2) {
            if ($engine->session->data->cropBonus != -1) {
                if ($engine->session->data->gold >= $price) {
                    $engine->account->edit("gold", $engine->session->data->gold - $price);
                    $duration = ($lifttime) ? -1 : (($engine->session->data->cropBonus != 0) ? $engine->session->data->cropBonus + $this->cropBonus_time : time() + $this->cropBonus_time);
                    query("UPDATE `{$engine->server->prefix}user` SET `cropBonus`=? WHERE `uid`=?;", [$duration, $engine->session->data->uid]);
                }
            }
        } elseif ($type == 3) {
            
        }
    }

    public function Logout() {
        global $engine;
        unset($_SESSION[$engine->server->prefix . 'uid']);
        unset($_SESSION[$engine->server->prefix . 'username']);
        unset($_COOKIE[$engine->server->prefix . 'vselect']);
        header("Location: ../../?lobby");
        exit();
    }

    public function gameCard() {
        global $engine;


        $r = [
            1 => [
                'rewardTypeId' => 7,
                'rewardAmount' => 1,
            ],
            [
                'rewardTypeId' => 11,
                'rewardAmount' => 1,
            ],
            [
                'rewardTypeId' => 13,
                'rewardAmount' => 1,
            ],
            [
                'rewardTypeId' => 10,
                'rewardAmount' => 1,
            ],
            [
                'rewardTypeId' => 20,
                'rewardAmount' => 1,
            ]
        ];
        return $r;
    }

    public function selectCard($params) {
        global $engine;
    }

    public function voucher() {
        global $engine;
    }

}
