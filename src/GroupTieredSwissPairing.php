<?php namespace haugstrup\TournamentUtils;

require_once 'Base.php';

class GroupTieredSwissPairing extends Base {

  public $rounds = 5;
  public $players = array();
  public $group_maps = array();
  public $max_players = 128;
  protected $min_players = 16;

  public function __construct($rounds, $players, $options = array()) {
    $this->players = $players;
    $this->rounds = (int)$rounds;
    if (isset($options['max_players'])) {
      $this->max_players = $options['max_players'];
    }

    $this->init_group_maps();

    if (count($this->players) < $this->min_players) {
      throw new \Exception('You must have at least 16 players');
    }
  }

  public function init_group_maps() {
    $maps = array(
      5 => array(
        16 => array(16, 16, 8, 8, 4),
        20 => array(20, array(12, 8), array(12, 8), array(8, 8, 4), 4),
        24 => array(24, 12, 12, 8, 4),
        28 => array(28, array(16, 12), array(16, 12), array(8, 8, 8, 4), 4),
        32 => array(32, 16, 16, 8, 4),
        36 => array(36, array(20, 16), 12, array(8, 8, 8, 8, 4), 4),
        40 => array(40, 20, array(16, 16, 8), 8, 4),
        44 => array(44, array(24, 20), array(16, 16, 12), array(8, 8, 8, 8, 8, 4), 4),
        48 => array(48, 24, 16, 8, 4),
        52 => array(52, array(28, 24), array(20, 20, 12), array(8, 8, 8, 8, 8, 8, 4), 4),
        56 => array(56, 28, array(16, 16, 16, 8), 8, 4),
        60 => array(60, 20, 12, array(8, 8, 8, 8, 8, 8, 8, 4), 4),
        64 => array(64, 32, 16, 8, 4),
        68 => array(68, array(36, 32), array(20, 16, 16, 16), array(12, 12, 12, 12, 12, 8), 4),
        72 => array(72, 36, 24, 12, 4),
        76 => array(76, array(40, 36), array(20, 20, 20, 16), array(16, 16, 16, 16, 12), 4),
        80 => array(80, 40, 20, 16, 4),
        84 => array(84, array(44, 40), array(24, 20, 20, 20), array(16, 16, 16, 16, 12, 8), 4),
        88 => array(88, 44, array(24, 24, 20, 20), array(16, 16, 16, 16, 16, 8), 4),
        92 => array(92, array(48, 44), array(24, 24, 24, 20), array(16, 16, 16, 16, 16, 12), 4),
        96 => array(96, 48, 24, 16, 4),
        100 => array(100, array(52, 48), array(28, 24, 24, 24), array(16, 16, 16, 16, 16, 12, 8), 4),
        104 => array(104, 52, array(28, 28, 24, 24), array(16, 16, 16, 16, 16, 16, 8), 4),
        108 => array(108, array(56, 52), array(28, 28, 28, 24), array(16, 16, 16, 16, 16, 16, 12), 4),
        112 => array(112, 56, 28, 16, 4),
        116 => array(116, array(60, 56), array(32, 28, 28, 28), array(16, 16, 16, 16, 16, 16, 12, 8), 4),
        120 => array(120, 60, array(32, 32, 28, 28), array(16, 16, 16, 16, 16, 16, 16, 8), 4),
        124 => array(124, array(64, 60), array(32, 32, 32, 28), array(16, 16, 16, 16, 16, 16, 16, 12), 4),
        128 => array(128, 64, 32, 16, 4),
      ),
      
      // 6 round tiered swiss, made by Morten Søbyskogen
      6 => array(
        16 => array(16, 16, 8, 8, 8, 4),
        20 => array(20, array(12, 8), array(12, 8), array(12, 8), array(8, 8, 4), 4),
        24 => array(24, 12,12, 8,8, 4),
        28 => array(28, array(16, 12), array(16, 12),array(8,8,8,4), array(8, 8, 8, 4),4),
        32 => array(32, 16, 16, 8, 8, 4),
        36 => array(36, array(20, 16),array(20, 16), 12, array(8, 8, 8, 8, 4), 4),
        40 => array(40, 20, 20, 8, 8, 4),
        44 => array(44, array(24, 20),array(24, 20), array(16, 16, 12), array(8, 8, 8, 8, 8, 4), 4),
        48 => array(48, 24, 24, 16, 8, 4),
      ),
      
      // 7 round tiered swiss arrays, made by Morten Søbyskogen (dont blame Andreas ;))
      7 => array(
        16 => array(16, 16, 16, 8, 8, 8, 4),
        20 => array(20, array(12, 8), array(12, 8), array(12, 8), array(8, 8, 4), array(8, 8, 4), 4),
        24 => array(24, 12,12, 12,8, 8, 4),
        28 => array(28, array(16, 12), array(16, 12),array(16, 12), array(8, 8, 8, 4), array(8, 8, 8, 4),4),
        32 => array(32, 16, 16, 16, 8, 8, 4),
        36 => array(36, array(20, 16),array(20, 16), 12, array(8, 8, 8, 8, 4), array(8, 8, 8, 8, 4),4),
        40 => array(40, 20, 20,array(16, 16, 8), 8, 8, 4),
        44 => array(44, array(24, 20),array(24, 20), array(16, 16, 12), array(8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 4),4),
        48 => array(48, 24, 24, 16, 8, 8, 4),
        52 => array(52, array(28, 24), array(28, 24),array(20, 20, 12), array(8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 4),4),
        56 => array(56, 28,28, array(16, 16, 16, 8), 8, 8, 4),
        60 => array(60, 20, 20, 12, array(8, 8, 8, 8, 8, 8, 8, 4),array(8, 8, 8, 8, 8, 8, 8, 4), 4),
        64 => array(64, 32, 32, 16, 8, 8, 4),
        68 => array(68, array(36, 32), array(36, 32),array(20, 16, 16, 16), array(12, 12, 12, 12, 12, 8), array(12, 12, 12, 12, 12, 8),4),
        72 => array(72, 36, 36, 24, 12, 12, 4),
        76 => array(76, array(40, 36), array(40, 36),array(20, 20, 20, 16), array(16, 16, 16, 16, 12), array(16, 16, 16, 16, 12),4),
        80 => array(80, 40, 40, 20, 16,16, 4),
        84 => array(84, array(44, 40), array(44, 40), array(24, 20, 20, 20), array(16, 16, 16, 16, 12, 8), array(16, 16, 16, 16, 12, 8),4),
        88 => array(88, 44, 44, array(24, 24, 20, 20), array(16, 16, 16, 16, 16, 8), array(16, 16, 16, 16, 16, 8),4),
        92 => array(92, array(48, 44), array(48, 44),array(24, 24, 24, 20), array(16, 16, 16, 16, 16, 12), array(16, 16, 16, 16, 16, 12),4),
        96 => array(96, 48, 48, 24, 16, 16, 4),
        100 => array(100, array(52, 48), array(52, 48),array(28, 24, 24, 24), array(16, 16, 16, 16, 16, 12, 8), array(16, 16, 16, 16, 16, 12, 8),4),
        104 => array(104, 52, 52, array(28, 28, 24, 24), array(16, 16, 16, 16, 16, 16, 8), array(16, 16, 16, 16, 16, 16, 8),4),
        108 => array(108, array(56, 52), array(56, 52),array(28, 28, 28, 24), array(16, 16, 16, 16, 16, 16, 12), array(16, 16, 16, 16, 16, 16, 12),4),
        112 => array(112, 56, 56, 28, 16, 16, 4),
        116 => array(116, array(60, 56), array(60, 56),array(32, 28, 28, 28), array(16, 16, 16, 16, 16, 16, 12, 8),array(16, 16, 16, 16, 16, 16, 12, 8), 4),
        120 => array(120, 60, 60, array(32, 32, 28, 28), array(16, 16, 16, 16, 16, 16, 16, 8), array(16, 16, 16, 16, 16, 16, 16, 8),4),
        124 => array(124, array(64, 60), array(64, 60),array(32, 32, 32, 28), array(16, 16, 16, 16, 16, 16, 16, 12), array(16, 16, 16, 16, 16, 16, 16, 12), 4),
        128 => array(128, 64, 64, 32, 16, 16, 4),
      ),

      10 => array(
        16 => array(16, 16, 16, 16, 8, 8, 8, 8, 8, 4),
        20 => array(20, array(12, 8), array(12, 8), array(12, 8), array(12, 8), array(8, 8, 4), array(8, 8, 4), array(8, 8, 4), array(8, 8, 4), 4),
        24 => array(24, 12, 12, 12, 12, 8, 8, 8, 8, 4),
        28 => array(28, array(16, 12), array(16, 12), array(16, 12), array(16, 12), array(8, 8, 8, 4), array(8, 8, 8, 4), array(8, 8, 8, 4), array(8, 8, 8, 4), 4),
        32 => array(32, 16, 16, 16, 16, 8, 8, 8, 8, 4),
        36 => array(36, array(20, 16), array(20, 16), 12, 12, 12, array(8, 8, 8, 8, 4), array(8, 8, 8, 8, 4), array(8, 8, 8, 8, 4), 4),
        40 => array(40, 20, 20, array(16, 16, 8), array(16, 16, 8), array(16, 16, 8), 8, 8, 8, 4),
        44 => array(44, array(24, 20), array(24, 20), array(16, 16, 12), array(16, 16, 12), array(16, 16, 12), array(8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 4), 4),
        48 => array(48, 24, 24, 16, 16, 16, 8, 8, 8, 4),
        52 => array(52, array(28, 24), array(28, 24), array(20, 20, 12), array(20, 20, 12), array(20, 20, 12), array(8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 4), 4),
        56 => array(56, 28, 28, array(16, 16, 16, 8), array(16, 16, 16, 8), array(16, 16, 16, 8), 8, 8, 8, 4),
        60 => array(60, 20, 20, 12, 12, 12, array(8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 4), 4),
        64 => array(64, 32, 32, 16, 16, 16, 8, 8, 8, 4),
        68 => array(68, array(36, 32), array(36, 32), array(20, 16, 16, 16), array(20, 16, 16, 16), array(20, 16, 16, 16), array(12, 12, 12, 12, 12, 8), array(12, 12, 12, 12, 12, 8), array(8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        72 => array(72, 36, 36, 24, 24, 24, 12, 12, array(8, 8, 8, 8, 8, 8, 8, 8, 4, 4), 4),
        76 => array(76, array(40, 36), array(40, 36), array(20, 20, 20, 16), array(20, 20, 20, 16), array(20, 20, 20, 16), array(16, 16, 16, 16, 12), array(16, 16, 16, 16, 12), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        80 => array(80, 40, 40, 20, 20, 20, 16, 16, 8, 4),
        84 => array(84, array(44, 40), array(44, 40), array(24, 20, 20, 20), array(24, 20, 20, 20), array(24, 20, 20, 20), array(16, 16, 16, 16, 12, 8), array(16, 16, 16, 16, 12, 8), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        88 => array(88, 44, 44, array(24, 24, 20, 20), array(24, 24, 20, 20), array(24, 24, 20, 20), array(16, 16, 16, 16, 16, 8), array(16, 16, 16, 16, 16, 8), 8, 4),
        92 => array(92, array(48, 44), array(48, 44), array(24, 24, 24, 20), array(24, 24, 24, 20), array(24, 24, 24, 20), array(16, 16, 16, 16, 16, 12), array(16, 16, 16, 16, 16, 12), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        96 => array(96, 48, 48, 24, 24, 24, 16, 16, 8, 4),
        100 => array(100, array(52, 48), array(52, 48), array(28, 24, 24, 24), array(28, 24, 24, 24), array(28, 24, 24, 24), array(16, 16, 16, 16, 16, 12, 8), array(16, 16, 16, 16, 16, 12, 8), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        104 => array(104, 52, 52, array(28, 28, 24, 24), array(28, 28, 24, 24), array(28, 28, 24, 24), array(16, 16, 16, 16, 16, 16, 8), array(16, 16, 16, 16, 16, 16, 8), 8, 4),
        108 => array(108, array(56, 52), array(56, 52), array(28, 28, 28, 24), array(28, 28, 28, 24), array(28, 28, 28, 24), array(16, 16, 16, 16, 16, 16, 12), array(16, 16, 16, 16, 16, 16, 12), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        112 => array(112, 56, 56, 28, 28, 28, 16, 16, 8, 4),
        116 => array(116, array(60, 56), array(60, 56), array(32, 28, 28, 28), array(32, 28, 28, 28), array(32, 28, 28, 28), array(16, 16, 16, 16, 16, 16, 12, 8), array(16, 16, 16, 16, 16, 16, 12, 8), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        120 => array(120, 60, 60, array(32, 32, 28, 28), array(32, 32, 28, 28), array(32, 32, 28, 28), array(16, 16, 16, 16, 16, 16, 16, 8), array(16, 16, 16, 16, 16, 16, 16, 8), 8, 4),
        124 => array(124, array(64, 60), array(64, 60), array(32, 32, 32, 28), array(32, 32, 32, 28), array(32, 32, 32, 28), array(16, 16, 16, 16, 16, 16, 16, 12), array(16, 16, 16, 16, 16, 16, 16, 12), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        128 => array(128, 64, 64, 32, 32, 32, 16, 16, 8, 4),
      ),

      12 => array(
        16 => array(16, 16, 16, 16, 16, 16, 8, 8, 8, 8, 8, 4),
        20 => array(20, array(12, 8), array(12, 8), array(12, 8), array(12, 8), array(12, 8), array(12, 8), array(8, 8, 4), array(8, 8, 4), array(8, 8, 4), array(8, 8, 4), 4),
        24 => array(24, 12, 12, 12, 12, 12, 12, 8, 8, 8, 8, 4),
        28 => array(28, array(16, 12), array(16, 12), array(16, 12), array(16, 12), array(16, 12), array(16, 12), array(8, 8, 8, 4), array(8, 8, 8, 4), array(8, 8, 8, 4), array(8, 8, 8, 4), 4),
        32 => array(32, 16, 16, 16, 16, 16, 16, 8, 8, 8, 8, 4),
        36 => array(36, array(20, 16), array(20, 16), array(20, 16), 12, 12, 12, 12, array(8, 8, 8, 8, 4), array(8, 8, 8, 8, 4), array(8, 8, 8, 8, 4), 4),
        40 => array(40, 20, 20, 20, array(16, 16, 8), array(16, 16, 8), array(16, 16, 8), array(16, 16, 8), 8, 8, 8, 4),
        44 => array(44, array(24, 20), array(24, 20), array(24, 20), array(16, 16, 12), array(16, 16, 12), array(16, 16, 12), array(16, 16, 12), array(8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 4), 4),
        48 => array(48, 24, 24, 24, 16, 16, 16, 16, 8, 8, 8, 4),
        52 => array(52, array(28, 24), array(28, 24), array(28, 24), array(20, 20, 12), array(20, 20, 12), array(20, 20, 12), array(20, 20, 12), array(8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 4), 4),
        56 => array(56, 28, 28, 28, array(16, 16, 16, 8), array(16, 16, 16, 8), array(16, 16, 16, 8), array(16, 16, 16, 8), 8, 8, 8, 4),
        60 => array(60, 20, 20, 20, 12, 12, 12, 12, array(8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 4), 4),
        64 => array(64, 32, 32, 32, 16, 16, 16, 16, 8, 8, 8, 4),
        68 => array(68, array(36, 32), array(36, 32), array(36, 32), array(20, 16, 16, 16), array(20, 16, 16, 16), array(20, 16, 16, 16), array(12, 12, 12, 12, 12, 8), array(12, 12, 12, 12, 12, 8), array(8, 8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        72 => array(72, 36, 36, 36, 24, 24, 24, 12, 12, array(8, 8, 8, 8, 8, 8, 8, 8, 4, 4), array(8, 8, 8, 8, 8, 8, 8, 8, 4, 4), 4),
        76 => array(76, array(40, 36), array(40, 36), array(40, 36), array(20, 20, 20, 16), array(20, 20, 20, 16), array(20, 20, 20, 16), array(16, 16, 16, 16, 12), array(16, 16, 16, 16, 12), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        80 => array(80, 40, 40, 40, 20, 20, 20, 16, 16, 8, 8, 4),
        84 => array(84, array(44, 40), array(44, 40), array(44, 40), array(24, 20, 20, 20), array(24, 20, 20, 20), array(24, 20, 20, 20), array(16, 16, 16, 16, 12, 8), array(16, 16, 16, 16, 12, 8), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        88 => array(88, 44, 44, 44, array(24, 24, 20, 20), array(24, 24, 20, 20), array(24, 24, 20, 20), array(16, 16, 16, 16, 16, 8), array(16, 16, 16, 16, 16, 8), 8, 8, 4),
        92 => array(92, array(48, 44), array(48, 44), array(48, 44), array(24, 24, 24, 20), array(24, 24, 24, 20), array(24, 24, 24, 20), array(16, 16, 16, 16, 16, 12), array(16, 16, 16, 16, 16, 12), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        96 => array(96, 48, 48, 48, 24, 24, 24, 16, 16, 8, 8, 4),
        100 => array(100, array(52, 48), array(52, 48), array(52, 48), array(28, 24, 24, 24), array(28, 24, 24, 24), array(28, 24, 24, 24), array(16, 16, 16, 16, 16, 12, 8), array(16, 16, 16, 16, 16, 12, 8), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        104 => array(104, 52, 52, 52, array(28, 28, 24, 24), array(28, 28, 24, 24), array(28, 28, 24, 24), array(16, 16, 16, 16, 16, 16, 8), array(16, 16, 16, 16, 16, 16, 8), 8, 8, 4),
        108 => array(108, array(56, 52), array(56, 52), array(56, 52), array(28, 28, 28, 24), array(28, 28, 28, 24), array(28, 28, 28, 24), array(16, 16, 16, 16, 16, 16, 12), array(16, 16, 16, 16, 16, 16, 12), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        112 => array(112, 56, 56, 56, 28, 28, 28, 16, 16, 8, 8, 4),
        116 => array(116, array(60, 56), array(60, 56), array(60, 56), array(32, 28, 28, 28), array(32, 28, 28, 28), array(32, 28, 28, 28), array(16, 16, 16, 16, 16, 16, 12, 8), array(16, 16, 16, 16, 16, 16, 12, 8), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        120 => array(120, 60, 60, 60, array(32, 32, 28, 28), array(32, 32, 28, 28), array(32, 32, 28, 28), array(16, 16, 16, 16, 16, 16, 16, 8), array(16, 16, 16, 16, 16, 16, 16, 8), 8, 8, 4),
        124 => array(124, array(64, 60), array(64, 60), array(64, 60), array(32, 32, 32, 28), array(32, 32, 32, 28), array(32, 32, 32, 28), array(16, 16, 16, 16, 16, 16, 16, 12), array(16, 16, 16, 16, 16, 16, 16, 12), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        128 => array(128, 64, 64, 64, 32, 32, 32, 16, 16, 8, 8, 4),
      ),

      13 => array(
        16 => array(16, 16, 16, 16, 16, 16, 8, 8, 8, 8, 8, 8, 4),
        20 => array(20, array(12, 8), array(12, 8), array(12, 8), array(12, 8), array(12, 8), array(12, 8), array(8, 8, 4), array(8, 8, 4), array(8, 8, 4), array(8, 8, 4), array(8, 8, 4), 4),
        24 => array(24, 12, 12, 12, 12, 12, 12, 8, 8, 8, 8, 8, 4),
        28 => array(28, array(16, 12), array(16, 12), array(16, 12), array(16, 12), array(16, 12), array(16, 12), array(8, 8, 8, 4), array(8, 8, 8, 4), array(8, 8, 8, 4), array(8, 8, 8, 4), array(8, 8, 8, 4), 4),
        32 => array(32, 16, 16, 16, 16, 16, 16, 8, 8, 8, 8, 8, 4),
        36 => array(36, array(20, 16), array(20, 16), array(20, 16), 12, 12, 12, 12, array(8, 8, 8, 8, 4), array(8, 8, 8, 8, 4), array(8, 8, 8, 8, 4), array(8, 8, 8, 8, 4), 4),
        40 => array(40, 20, 20, 20, array(16, 16, 8), array(16, 16, 8), array(16, 16, 8), array(16, 16, 8), 8, 8, 8, 8, 4),
        44 => array(44, array(24, 20), array(24, 20), array(24, 20), array(16, 16, 12), array(16, 16, 12), array(16, 16, 12), array(16, 16, 12), array(8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 4), 4),
        48 => array(48, 24, 24, 24, 16, 16, 16, 16, 8, 8, 8, 8, 4),
        52 => array(52, array(28, 24), array(28, 24), array(28, 24), array(20, 20, 12), array(20, 20, 12), array(20, 20, 12), array(20, 20, 12), array(8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 4), 4),
        56 => array(56, 28, 28, 28, array(16, 16, 16, 8), array(16, 16, 16, 8), array(16, 16, 16, 8), array(16, 16, 16, 8), 8, 8, 8, 8, 4),
        60 => array(60, 20, 20, 20, 12, 12, 12, 12, array(8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 4), 4),
        64 => array(64, 32, 32, 32, 16, 16, 16, 16, 8, 8, 8, 8, 4),
        68 => array(68, array(36, 32), array(36, 32), array(36, 32), array(20, 16, 16, 16), array(20, 16, 16, 16), array(20, 16, 16, 16), array(12, 12, 12, 12, 12, 8), array(12, 12, 12, 12, 12, 8), array(12, 12, 12, 12, 12, 8), array(8, 8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        72 => array(72, 36, 36, 36, 24, 24, 24, 12, 12, 12, array(8, 8, 8, 8, 8, 8, 8, 8, 4, 4), array(8, 8, 8, 8, 8, 8, 8, 8, 4, 4), 4),
        76 => array(76, array(40, 36), array(40, 36), array(40, 36), array(20, 20, 20, 16), array(20, 20, 20, 16), array(20, 20, 20, 16), array(16, 16, 16, 16, 12), array(16, 16, 16, 16, 12), array(16, 16, 16, 16, 12), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        80 => array(80, 40, 40, 40, 20, 20, 20, 16, 16, 16, 8, 8, 4),
        84 => array(84, array(44, 40), array(44, 40), array(44, 40), array(24, 20, 20, 20), array(24, 20, 20, 20), array(24, 20, 20, 20), array(16, 16, 16, 16, 12, 8), array(16, 16, 16, 16, 12, 8), array(16, 16, 16, 16, 12, 8), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        88 => array(88, 44, 44, 44, array(24, 24, 20, 20), array(24, 24, 20, 20), array(24, 24, 20, 20), array(16, 16, 16, 16, 16, 8), array(16, 16, 16, 16, 16, 8), array(16, 16, 16, 16, 16, 8), 8, 8, 4),
        92 => array(92, array(48, 44), array(48, 44), array(48, 44), array(24, 24, 24, 20), array(24, 24, 24, 20), array(24, 24, 24, 20), array(16, 16, 16, 16, 16, 12), array(16, 16, 16, 16, 16, 12), array(16, 16, 16, 16, 16, 12), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        96 => array(96, 48, 48, 48, 24, 24, 24, 16, 16, 16, 8, 8, 4),
        100 => array(100, array(52, 48), array(52, 48), array(52, 48), array(28, 24, 24, 24), array(28, 24, 24, 24), array(28, 24, 24, 24), array(16, 16, 16, 16, 16, 12, 8), array(16, 16, 16, 16, 16, 12, 8), array(16, 16, 16, 16, 16, 12, 8), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        104 => array(104, 52, 52, 52, array(28, 28, 24, 24), array(28, 28, 24, 24), array(28, 28, 24, 24), array(16, 16, 16, 16, 16, 16, 8), array(16, 16, 16, 16, 16, 16, 8), array(16, 16, 16, 16, 16, 16, 8), 8, 8, 4),
        108 => array(108, array(56, 52), array(56, 52), array(56, 52), array(28, 28, 28, 24), array(28, 28, 28, 24), array(28, 28, 28, 24), array(16, 16, 16, 16, 16, 16, 12), array(16, 16, 16, 16, 16, 16, 12), array(16, 16, 16, 16, 16, 16, 12), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        112 => array(112, 56, 56, 56, 28, 28, 28, 16, 16, 16, 8, 8, 4),
        116 => array(116, array(60, 56), array(60, 56), array(60, 56), array(32, 28, 28, 28), array(32, 28, 28, 28), array(32, 28, 28, 28), array(16, 16, 16, 16, 16, 16, 12, 8), array(16, 16, 16, 16, 16, 16, 12, 8), array(16, 16, 16, 16, 16, 16, 12, 8), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        120 => array(120, 60, 60, 60, array(32, 32, 28, 28), array(32, 32, 28, 28), array(32, 32, 28, 28), array(16, 16, 16, 16, 16, 16, 16, 8), array(16, 16, 16, 16, 16, 16, 16, 8), array(16, 16, 16, 16, 16, 16, 16, 8), 8, 8, 4),
        124 => array(124, array(64, 60), array(64, 60), array(64, 60), array(32, 32, 32, 28), array(32, 32, 32, 28), array(32, 32, 32, 28), array(16, 16, 16, 16, 16, 16, 16, 12), array(16, 16, 16, 16, 16, 16, 16, 12), array(16, 16, 16, 16, 16, 16, 16, 12), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), array(8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 4), 4),
        128 => array(128, 64, 64, 64, 32, 32, 32, 16, 16, 16, 8, 8, 4),
      ),


      // Pinball at the lake, 7 round tiers
      // 7 => array(
      //   16 => array(16, 16, 16, 16, 8, 8, 8),
      //   20 => array(20, 20, 20, array(12, 8), array(12, 8), array(8, 8, 4), array(8, 8, 4)),
      //   24 => array(24, 24, 24, 12, 12, 8, 8),
      //   28 => array(28, 28, 28, array(16, 12), array(16, 12), array(8, 8, 8, 4), array(8, 8, 8, 4)),
      //   32 => array(32, 32, 32, 16, 16, 8, 8),
      //   36 => array(36, array(20, 16), array(20, 16), 12, 12, array(8, 8, 8, 8, 4), array(8, 8, 8, 8, 4)),
      //   40 => array(40, 20, 20, array(16, 16, 8), array(16, 16, 8), 8, 8),
      //   44 => array(44, array(24, 20), array(24, 20), array(16, 16, 12), array(16, 16, 12), array(8, 8, 8, 8, 8, 4)),
      //   48 => array(48, 24, 24, 16, 16, 8, 8),
      // )
    );

    // Prep group maps for 3 and 4 rounds
    foreach ($maps[5] as $index => $tiers) {

      // For three rounds, remove index 1, 2
      $three_round_tiers = $tiers;
      array_splice($three_round_tiers, 1, 2);

      // For four rounds, remove index 2 (the middle round)
      $four_round_tiers = $tiers;
      array_splice($four_round_tiers, 2, 1);

      $maps[3][$index] = $three_round_tiers;
      $maps[4][$index] = $four_round_tiers;
    }

    $this->group_maps = $maps;
  }

  public function get_group_map() {
    $player_count = count($this->players);
    if (isset($this->group_maps[$this->rounds][$player_count])) {
      $map = $this->group_maps[$this->rounds][$player_count];
      $key = $player_count;
    }
    else {
      $i = $player_count;
      while ($i <= $this->max_players) {
        if (isset($this->group_maps[$this->rounds][$i])) {
          $map = $this->group_maps[$this->rounds][$i];
          $key = $i;
          break;
        }
        $i++;
      }
    }

    if (!$map) {
      throw new \Exception('Couldn\'t find tier map');
    }

    return array('map' => $map, 'key' => $key);
  }

  // Find the specific map for a specific round
  public function get_round_map($round) {
    $map = $this->get_group_map();
    $round_map = $map['map'][$round];
    if (is_int($round_map)) {
      $round_map = array_pad(array(), $map['key']/$round_map, $round_map);
    }
    return $round_map;
  }

  // Build groups for a specific round
  public function build($round) {
    $players = $this->players;
    $player_count = count($this->players);
    $groups = array();

    // Stay within boundaries
    if ($round >= $this->rounds || $round < 0) {
      throw new \Exception('Too many or too few rounds');
    }
    if ($player_count > $this->max_players || $player_count < 0) {
      throw new \Exception('Too many or too few players');
    }

    $round_map = $this->get_round_map($round);

    foreach ($round_map as $index => $size) {
      $number_of_players = $size;

      // If we're in the third to last group and...
      // ...there are 9 players left
      // ...all tiers are 4
      // Then make all three player groups
      if (count($round_map)-3 == $index && count($players) == 9 && $round_map[$index] === 4) {
        $number_of_players--;
      }


      // If we're in the second to last group and...
      // ...we need to create 2 three-player groups
      // ...or we need to create 3 three-player groups
      // Then decrease the amount of players we grab for second-to-last group
      if (count($round_map)-2 == $index) {

        // Remaining players == required left players-2
        if (count($players) === ($round_map[$index]+$round_map[$index+1]-2)) {
          if ($round_map[$index+1] === 4) {
            $number_of_players--;
          }
        }

        // Remaining players == required players-3
        if (count($players) === ($round_map[$index]+$round_map[$index+1]-3)) {

          if ($round_map[$index+1] === 8) {
            $number_of_players--;
          }

          // Next round calls for 4 players
          if ($round_map[$index+1] === 4) {
            $number_of_players--;
            $number_of_players--;
          }
        }

      }

      $tier_players = array_splice($players, 0, $number_of_players);

      // Get people into groups.
      // Nb. if there's only 6 players left, create two 3 player groups
      $number_of_groups = $size/4;

      for($j=0;$j<$number_of_groups;$j++) {
        $three_player_group = count($tier_players) == 9 || count($tier_players) == 6 || count($tier_players) == 3;
        $middle_offset = ceil(count($tier_players)/2)-2;

        // Grab the first, middle and last players
        $group = array();
        $group = array_merge($group, array_splice($tier_players, 0, 1));
        $group = array_merge($group, array_splice($tier_players, $middle_offset, $three_player_group ? 1 : 2));
        $group = array_merge($group, array_splice($tier_players, -1, 1));

        $groups[] = $group;
      }

    }

    return $groups;
  }

}
