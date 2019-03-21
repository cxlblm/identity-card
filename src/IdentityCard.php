<?php
/**
 * Created by PhpStorm.
 * User: cxlblm
 * Date: 2019/3/13
 * Time: 13:44
 */

namespace Cxlblm;

use DateTimeImmutable;

class IdentityCard
{
    protected const WEIGHT = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
    protected const TOKEN = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
    protected const LENGTH = 17;
    protected const MODEL = 11;

    protected static $genderDesc = [0 => '女', 1 => '男'];
    public static $withException = false;
    private static $dateFormat;
    protected static $zodiacSign = [
        /*
        1 => 'aquarius',
        2 => 'pisces',
        3 => 'aries',
        4 => 'taurus',
        5 => 'gemini',
        6 => 'cancer',
        7 => 'leo',
        8 =>'virgo',
        9 =>'libra',
        10 =>'scorpio',
        11 => 'sagittarius',
        12 => 'capricorn',
        */
        1 => '水瓶座',
        2 => '双鱼座',
        3 => '牡羊座',
        4 => '金牛座',
        5 => '双子座',
        6 => '巨蟹座',
        7 => '狮子座',
        8 => '处女座',
        9 => '天秤座',
        10 => '天蝎座',
        11 => '射手座',
        12 => '摩羯座',
    ];

    protected const ZODIAC_DATE = [
        1 => [[1, 21], [2, 18]],
        2 => [[2, 19], [3, 20]],
        3 => [[3, 21], [4, 20]],
        4 => [[4, 21], [5, 21]],
        5 => [[5, 22], [6, 21]],
        6 => [[6, 22], [7, 22]],
        7 => [[7, 23], [8, 23]],
        8 => [[8, 24], [9, 22]],
        9 => [[9, 23], [10, 23]],
        10 => [[10, 24], [11, 22]],
        11 => [[11, 23], [12, 21]],
        12 => [[12, 22], [1, 20]],
    ];

    private $areaCode = [];
    private $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @param string $id
     * @return bool|IdentityCard
     * @throws IdentityException
     */
    public static function make(string $id)
    {
        $instance = new static($id);
        if ($instance->fail()) {
            if (static::$withException) {
                throw new IdentityException('错误的证件号码');
            }
            return false;
        }
        return $instance;
    }

    /**
     * @param callable|null $callback
     */
    public static function setDateFormat($format)
    {
        static::$dateFormat = $format;
    }

    /**
     * @return callable|null
     */
    public static function dateFormat(): ?callable
    {
        return static::$dateFormat;
    }

    /**
     * @param bool $bool
     */
    public static function failWithException(bool $bool)
    {
        static::$withException = $bool;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        if (strlen($this->id) != 18) {
            return false;
        }
        if (! preg_match('~^[1-9][0-9]{16}([0-9]|[Xx])~', $this->id)) {
            return false;
        }
        $sum = 0;
        for ($i = 0; $i < static::LENGTH; ++$i) {
            $sum += $this->id[$i] * static::WEIGHT[$i];
        }
        $mode = $sum % static::MODEL;
        return static::TOKEN[$mode] === strtoupper($this->id[17]);
    }

    /**
     * @return bool
     */
    public function fail(): bool
    {
        return ! $this->valid();
    }

    private function birthdayString(): string
    {
        return substr($this->id, 6, 8);
    }

    /**
     * @return bool|string|object
     */
    public function birthday()
    {
        $birthday = $this->birthdayString();
        $format = static::$dateFormat;
        if (is_callable($format)) {
            return $format($birthday);
        } elseif (is_string($format)) {
            return date(strtotime($birthday), $format);
        } else {
            return $birthday;
        }
    }

    /**
     * @return int
     */
    public function gender(): int
    {
        return $this->id[16] & 1;
    }

    /**
     * @return string
     */
    public function genderDesc(): string
    {
        return static::$genderDesc[$this->gender()];
    }

    /**
     * @param int $code
     * @return string|null
     */
    public function regionFromCode(int $code): ?string
    {
        $region = [
            1 => '华北区',
            2 => '东北区',
            3 => '华东区',
            4 => '中南区',
            5 => '西南区',
            6 => '西北区',
        ];
        return $region[$code] ?? null;
    }

    /**
     * @return int
     */
    public function regionCode(): int
    {
        return (int)$this->id[1];
    }

    /**
     * @return string
     */
    public function region(): ?string
    {
        return $this->regionFromCode($this->regionCode());
    }

    /**
     * @return int
     */
    public function provinceCode(): int
    {
        return ((int)substr($this->id, 0, 2)) * 10000;
    }

    /**
     * @return string|null
     */
    public function province(): ?string
    {
        return $this->getFromAreaCode($this->provinceCode());
    }

    /**
     * @return int
     */
    public function cityCode(): int
    {
        return ((int)substr($this->id, 0, 4)) * 100;
    }

    /**
     * @return string|null
     */
    public function city(): ?string
    {
        return $this->getFromAreaCode($this->cityCode());
    }

    /**
     * @return int
     */
    public function countyCode(): int
    {
        return ((int)substr($this->id, 0, 6));
    }

    /**
     * @return string|null
     */
    public function county(): ?string
    {
        return $this->getFromAreaCode($this->countyCode());
    }

    private function loadRegion(): void
    {
        if (empty($this->areaCode) && file_exists(__DIR__ . '/../data/area.php')) {
            $this->areaCode = (array)include __DIR__ . '/../data/area.php';
        }
    }

    /**
     * @param int $code
     * @return string|null
     */
    public function getFromAreaCode(int $code): ?string
    {
        $this->loadRegion();
        return $this->areaCode[$code] ?? null;
    }

    /**
     * 星座
     * @return string
     * @throws \Exception
     */
    public function zodiac(): string
    {
        $birth = new DateTimeImmutable($this->birthdayString());
        foreach (static::ZODIAC_DATE as $key => $item) {
            if (
                $birth >= $birth->setDate((int)$birth->format('Y'), ...$item[0])
                && $birth <= $birth->setDate((int)$birth->format('Y'), ...$item[1])
            ) {
                return static::$zodiacSign[$key];
            }
        }
        return '';
    }

    /**
     * 生肖
     */
    public function animalSign()
    {

    }
}
