<?php

ini_set('log_errors', 'on'); //ログを取るか
ini_set('error_log,', 'php.log'); //ログ出力ファイルを指定
session_start(); //セッション使用

//恐竜達格納用
$dinosaurs = array();

//性別クラス
class Sex
{
  const MAN = 1;
  const WOMAN = 2;
}

//抽象クラス(生き物クラス)
abstract class Creature
{
  protected $name;
  protected $hp;
  protected $attackMin;
  protected $attackMax;
  abstract public function sayCry();
  public function setName($str)
  {
    $this->name = $str;
  }

  public function getName()
  {
    return $this->name;
  }

  public function setHp($num)
  {
    $this->hp = $num;
  }

  public function getHp()
  {
    return $this->hp;
  }

  public function attack($targetObj)
  {
    $attackPoint = mt_rand($this->attackMin, $this->attackMax);
    if (!mt_rand(0, 9)) {
      $attackPoint = $attackPoint * 1.5;
      $attackPoint = (int) $attackPoint; //整数型にする
      History::set($this->getName() . 'のクリティカルヒット！！');
    }
    $targetObj->setHp($targetObj->getHp() - $attackPoint);
    History::set($attackPoint . 'ポイントのダメージ！');
  }
}

//ハンタークラス
class Hunter extends Creature
{
  protected $sex;
  protected $img;
  public function __construct($name, $sex, $img, $hp, $attackMin, $attackMax)
  {
    $this->name = $name;
    $this->sex = $sex;
    $this->img = $img;
    $this->hp = $hp;
    $this->attackMin = $attackMin;
    $this->attackMax = $attackMax;
  }
  public function setSex($num)
  {
    $this->sex = $num;
  }

  public function getSex()
  {
    return $this->sex;
  }

  public function sayCry()
  {
    switch ($this->sex) {
      case Sex::MAN:
        History::set('危ない！！');
        break;
      case Sex::WOMAN:
        History::set('ちょっと！！');
        break;
    }
  }
}

//ダイナソークラス
class Dinosaur extends Creature
{
  //プロパティ
  protected $img;
  //コンストラクタ
  public function __construct($name, $hp, $img, $attackMin, $attackMax)
  {
    $this->name = $name;
    $this->hp = $hp;
    $this->img = $img;
    $this->attackMin = $attackMin;
    $this->attackMax = $attackMax;
  }

  //ゲッター
  public function getImg()
  {
    return $this->img;
  }

  public function sayCry()
  {
    History::set($this->name . 'が怯んでいる！');
    History::set('がおおおおおおお！！');
  }
}

// 火を吹けるダイナソークラス
class FireDinosaur extends Dinosaur
{
  private $fireAttack;
  function __construct($name, $hp, $img, $attackMin, $attackMax, $fireAttack)
  {
    //親クラスのコンストラクタで処理する内容を継承したい場合には親コンストラクタを呼びます。
    parent::__construct($name, $hp, $img, $attackMin, $attackMax);
    $this->fireAttack = $fireAttack;
  }

  public function getFireAttack()
  {
    return  $this->fireAttack;
  }

  public function attack($targetObj)
  {
    if (!mt_rand(0, 4)) { //5分の1の確率で魔法攻撃
      History::set($this->name . 'の火炎放射！！');
      $targetObj->setHp($targetObj->getHp() - $this->fireAttack);
      History::set($this->fireAttack . 'ポイントのダメージを受けた！');
    } else {
      parent::attack($targetObj);
    }
  }
}

interface HistoryInterface
{
  public static function set($str);
  public static function clear();
}

//履歴管理クラス（インスタンス化して複数増殖させる必要のないクラスなので、staticにする）
class History implements HistoryInterface
{
  public static function set($str)
  {
    //セッションhistoryが作られてなければ作る
    if (empty($_SESSION['history'])) $_SESSION['history'] = '';
    //文字列をセッションhistoryへ格納
    $_SESSION['history'] .= $str . '<br>';
  }
  public static function clear()
  {
    unset($_SESSION['history']);
  }
}

//インスタンス生成
$hunter = new Hunter('ハンター', Sex::MAN, 'img/hunter.jpg', 500, 100, 200);
$dinosaurs[] = new Dinosaur('ステゴサウルス', 200, 'img/dinosaur01.png', 20, 40);
$dinosaurs[] = new FireDinosaur('スピノサウルス', 400, 'img/dinosaur02.png', 60, 80, mt_rand(80, 100));
$dinosaurs[] = new Dinosaur('アンキロサウルス', 300, 'img/dinosaur03.png', 30, 50);
$dinosaurs[] = new FireDinosaur('ヴェロキラプトル', 200, 'img/dinosaur04.png', 30, 50, mt_rand(40, 60));
$dinosaurs[] = new FireDinosaur('ティラノサウルス', 600, 'img/dinosaur05.png', 100, 150, mt_rand(120, 200));
$dinosaurs[] = new FireDinosaur('ディノ二クス', 300, 'img/dinosaur06.png', 60, 100, mt_rand(80, 110));
$dinosaurs[] = new Dinosaur('トリケラトプス', 400, 'img/dinosaur07.png', 50, 80);
$dinosaurs[] = new Dinosaur('ブラキオサウルス', 700, 'img/dinosaur08.png', 20, 50);
$dinosaurs[] = new Dinosaur('パキケファロサウルス', 300, 'img/dinosaur09.png', 100, 120);
$dinosaurs[] = new Dinosaur('パラサウロロフス', 200, 'img/dinosaur10.png', 10, 30);

function createDinosaur()
{
  global $dinosaurs;
  $dinosaur = $dinosaurs[mt_rand(0, 9)];
  History::set($dinosaur->getName() . 'が現れた！');
  $_SESSION['dinosaur'] = $dinosaur;
}

function createHunter()
{
  global $hunter;
  $_SESSION['hunter'] = $hunter;
}

function init()
{
  History::clear();
  History::set('ひと狩り行こうぜ！！！');
  $_SESSION['CatchCount'] = 0;
  createHunter();
  createDinosaur();
}

function gameOver()
{
  $_SESSION = array();
}

//1.POST送信された場合
if (!empty($_POST)) {
  $attackFlg = (!empty($_POST['attack'])) ? true : false;
  $startFlg = (!empty($_POST['start'])) ? true : false;
  $catchFlg = (!empty($_POST['catch'])) ? true : false;

  error_log('POSTされた！');

  if ($startFlg) {
    History::set('ゲームスタート');
    init();
  } else {
    //攻撃するを押した場合
    if ($attackFlg) {

      //モンスターに攻撃を与える
      History::set($_SESSION['hunter']->getName() . 'の攻撃！');
      $_SESSION['hunter']->attack($_SESSION['dinosaur']);
      $_SESSION['dinosaur']->sayCry();

      //モンスターが攻撃する
      History::set($_SESSION['dinosaur']->getName() . 'の攻撃！');
      $_SESSION['dinosaur']->attack($_SESSION['hunter']);
      $_SESSION['hunter']->sayCry();

      //モンスターのHPが100以下になったら表示
      if ($_SESSION['dinosaur']->getHp() <= 100) {
        History::set('弱っている！捕獲のチャンスだ！！');
      }

      //自分のHPが０以下になったらゲームオーバー
      if ($_SESSION['hunter']->getHp() <= 0) {
        gameOver();
      } else {
        //恐竜のHPが0以下になったら、別のモンスターを出現させる
        if ($_SESSION['dinosaur']->getHp() <= 0) {
          History::set($_SESSION['dinosaur']->getName() . 'を倒した！');
          createDinosaur();
        }
      }
    } elseif ($catchFlg) {

      if ($_SESSION['dinosaur']->getHp() >= 100) {
        History::set('だめだ。まだ捕獲できない！！');
      } else {
        if ($_SESSION['dinosaur']->getHp() <= 100) {
          History::set('よし！捕まえた！！');
          createDinosaur();
          $_SESSION['CatchCount'] = $_SESSION['CatchCount'] + 1;
        }
      }
    } else {
      History::set('うまく逃げられた!');
      createDinosaur();
    }
  }
  $_POST = array();
}

?>





<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Catch the dinosaur</title>
  <style>
    .game-start {
      margin-top: 99px;
      text-align: center;
      margin-left: 7px;
      position: relative;
      color: black;
    }

    .start {
      text-align: center;
      font-size: 16px;
      position: relative;
      margin-top: 10px;
      left: 585px;
      border: none;
      background-color: #DAA520;
      padding: 15px 30px;
    }

    .choice {
      border: none;
      padding: 10px 15px;
      left: 730px;
      bottom: 120px;
      display: flex;
      width: 100px;
      background-color: #DAA520;
      position: relative;
      font-size: 12px;
    }

    .BOXA {
      float: left;

    }

    input[type="submit"]:hover {
      background: Transparent;
      cursor: pointer;
      background-color: #800000;
    }

    input[type="submit"]:focus {
      outline: 0;
    }

    .dinosaur-hp {
      position: relative;
      left: 700px;
      bottom: 290px;
      color: black;
      font-size: 14px;
      background-color: #DAA520;
      width: 220px;
      text-align: center;
    }

    .hunter-hp {
      position: relative;
      left: 380px;
      bottom: 230px;
      color: black;
      font-size: 14px;
      background-color: #DAA520;
      width: 220px;
      text-align: center;
    }

    .catch-dinosaur {
      position: relative;
      left: 380px;
      bottom: -10px;
      color: black;
      font-size: 14px;
      background-color: #DAA520;
      width: 220px;
      text-align: center;
    }
  </style>
</head>

<body>
  <h1 style="text-align:center; color:black; font-size:24px;">Let's Hunting!!</h1>
  <?php if (empty($_SESSION)) { ?>
    <img src="img/wallpaper.jpg" style="width:420px; height:auto; margin:auto; top: 0; left: 0; right: 0; bottom: 250px; position:absolute;">
    <h2 class="game-start" style="">GAME START ?</h2>
    <form method="post">
      <input class="start" type="submit" name="start" value="出発">
    </form>
  <?php } else { ?>
    <h2 class="dinosaur-emergence" style="margin-top:30px; margin-left:350px; font-size:18px;">
      <?php echo $_SESSION['dinosaur']->getName() . 'が現れた!!'; ?>
    </h2>
    <img src="img/wallpaper.jpg" style="width: 600px; height:auto; position:absolute; text-align:center; left:350px;">
    <img src="img/hunter.png" style="width: 200px; height:auto; position:relative; left:420px; top:80px">
    <img src="<?php echo $_SESSION['dinosaur']->getImg(); ?>" style="width:280px; position:relative; left:430px;">
    <p class="dinosaur-hp">
      <?php echo $_SESSION['dinosaur']->getName() . 'のHP '; ?>:
      <?php echo $_SESSION['dinosaur']->getHp(); ?>
    </p>
    <p class="catch-dinosaur">捕まえたダイナソーの数:
      <?php echo $_SESSION['CatchCount']; ?>
    </p>
    <p class="hunter-hp">ハンターの残りのHP:
      <?php echo $_SESSION['hunter']->getHp(); ?>
    </p>
    <form method="post">
      <div class="menu">
        <div class="BOXA">
          <input class="choice" type="submit" name="attack" value="▶︎銃で撃つ">
          <input class="choice" type="submit" name="escape" value="▶︎逃げる">
        </div>
        <div class="BOXA">
          <input class="choice" type="submit" name="catch" value="▶︎捕獲する">
          <input class="choice" type="submit" name="start" value="▶︎はじめから">
        </div>
      </div>
    </form>
  <?php } ?>
  <div style="position:absolute; right:20px; top:0; color:black; width: 300px;">
    <p>
      <?php echo (!empty($_SESSION['history'])) ? $_SESSION['history'] : ''; ?>
    </p>
  </div>
</body>

</html>