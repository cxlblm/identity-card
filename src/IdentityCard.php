<?php
/**
 * Created by PhpStorm.
 * User: cxlblm
 * Date: 2019/3/13
 * Time: 13:44
 */

namespace Cxlblm;


class IdentityCard
{
    protected const WEIGHT = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
    protected const TOKEN = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
    protected const LENGTH = 17;
    protected const MODEL = 11;
    protected static $genderDesc = [0 => '女', 1 => '男'];
    public static $withException = false;
    private static $dateFormat;
    private $areaCode = [];

    private $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

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

    public static function setDateFormat(?callable $callback)
    {
        static::$dateFormat = $callback;
    }

    public static function dateFormat(): ?callable
    {
        return static::$dateFormat;
    }

    public static function failWithException(bool $bool)
    {
        static::$withException = $bool;
    }

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
        return static::TOKEN[$mode] === strtoupper($this->id{17});
    }

    public function fail()
    {
        return ! $this->valid();
    }

    public function birthday()
    {
        $birthday = substr($this->id, 6, 8);
        return static::$dateFormat ? (static::$dateFormat)($birthday) : $birthday;
    }

    public function gender(): int
    {
        return $this->id{16} & 1;
    }

    public function genderDesc(): string
    {
        $gender = $this->gender();
        if (null === $gender) {
            return null;
        }
        return static::$genderDesc[$gender];
    }

    public function regionFromCode(int $code): string
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

    public function regionCode(): int
    {
        return (int) $this->id{1};
    }

    public function region(): string
    {
        return $this->regionFromCode($this->regionCode());
    }

    public function provinceCode(): int
    {
        return ((int) substr($this->id, 0, 2)) * 10000;
    }

    public function province(): ?string
    {
        return $this->getFromAreaCode($this->provinceCode());
    }

    public function cityCode(): int
    {
        return ((int) substr($this->id, 0, 4)) * 100;
    }

    public function city(): ?string
    {
        return $this->getFromAreaCode($this->cityCode());
    }

    public function countyCode(): int
    {

        return ((int) substr($this->id, 0, 6));
    }

    public function county(): ?string
    {
        return $this->getFromAreaCode($this->countyCode());
    }

    private function loadRegion(): void
    {
        if (empty($this->areaCode) && file_exists(__DIR__ . '/../data/area.php')) {
            $this->areaCode = (array) include __DIR__ . '/../data/area.php';
        }
    }

    public function getFromAreaCode(int $code): ?string
    {
        $this->loadRegion();
        return $this->areaCode[$code] ?? null;
    }
}
