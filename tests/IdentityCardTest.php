<?php
/**
 * Created by PhpStorm.
 * User: cxlblm
 * Date: 2019/3/15
 * Time: 9:33
 */

namespace Tests;

use Cxlblm\IdentityCard;
use Cxlblm\IdentityException;
use PHPUnit\Framework\TestCase;

class IdentityCardTest extends TestCase
{
    public function IDProvider()
    {
        return [
            ['512501197506045175'],
            ['512501196512305186'],
            ['410222198706134038'],
            ['410222198611270512'],
            ['410222197601236041'],
            ['410222197501154516'],
        ];
    }

    /**
     * @dataProvider IDProvider
     */
    public function testMake($id)
    {
        $identity = IdentityCard::make($id);
        $this->assertInstanceOf(IdentityCard::class, $identity);
        return $identity;
    }

    public function FailIDProvider()
    {
        return [
            ['410222197501154514'],
            ['410222197501153514'],
            ['410222197501152514'],
            ['41022219750115x514'],
            ['410222197501153514'],
            ['410222197501153514x'],
            ['413222197501154514'],
        ];
    }

    /**
     * @dataProvider FailIDProvider
     */
    public function testMakeFail($id)
    {
        $identity = IdentityCard::make($id);
        $this->assertEquals(false, $identity);
        return $identity;
    }

    /**
     * @dataProvider FailIDProvider
     */
    public function testMakeWithException($id)
    {
        $this->expectException(IdentityException::class);
        IdentityCard::failWithException(true);
        IdentityCard::make($id);
    }

    public function genderProvider()
    {
        return [
            ['41090119661207051X', 1],
            ['410901196812194541', 0],
            ['41092819680218063X', 1],
            ['410928196802180648', 0],
            ['41092819800921125X', 1],
            ['410901198307245511', 1],
            ['410926196406265217', 1]
        ];
    }

    /**
     * @dataProvider genderProvider
     */
    public function testGender($id, $gender)
    {
        $identity = $this->testMake($id, true);
        $this->assertEquals($gender, $identity->gender());
    }

    public function genderDescProvider()
    {
        return [
            ['41090119661207051X', '男'],
            ['410901196812194541', '女'],
            ['41092819680218063X', '男'],
            ['410928196802180648', '女'],
            ['41092819800921125X', '男'],
            ['410901198307245511', '男'],
            ['410926196406265217', '男']
        ];
    }

    /**
     * @dataProvider genderDescProvider
     */
    public function testGenderDesc($id, $gender)
    {
        $identity = $this->testMake($id, true);
        $this->assertEquals($gender, $identity->genderDesc());
    }

    public function birthdayProvider()
    {
        return [
            ['41090119661207051X', '19661207'],
            ['410901196812194541', '19681219'],
            ['41092819680218063X', '19680218'],
            ['410928196802180648', '19680218'],
            ['41092819800921125x', '19800921'],
            ['410901198307245511', '19830724'],
            ['410926196406265217', '19640626'],
        ];
    }

    /**
     * @dataProvider birthdayProvider
     */
    public function testBirthdayWithObject($id, $birthday)
    {
        IdentityCard::setDateFormat(function ($date) {
            return new \DateTime($date);
        });
        $identity = $this->testMake($id);
        $this->assertInstanceOf(\DateTime::class, $identity->birthday());
        $this->assertEquals(new \DateTime($birthday), $identity->birthday());
    }

    /**
     * @dataProvider birthdayProvider
     */
    public function testBirthday($id, $birthday)
    {
        IdentityCard::setDateFormat(null);
        $identity = $this->testMake($id, true);
        $this->assertEquals($birthday, $identity->birthday());
    }

    public function provinceProvider()
    {
        return [
            ['150624197307108592', '内蒙古自治区'],
            ['45032619840627183x', '广西壮族自治区'],
            ['533527198909210238', '云南省'],
            ['421001198301097785', '湖北省'],
            ['130825199105138665', '河北省'],
            ['141102198906264202', '山西省'],
            ['542522197407249014', '西藏自治区'],
        ];
    }

    /**
     * @dataProvider provinceProvider
     * @param $id
     * @param $province
     */
    public function testProvince($id, $province)
    {
        $identity = $this->testMake($id);
        $this->assertEquals($province, $identity->province());
    }

    public function cityProvider()
    {
        return [
            ['45032619840627183x', '桂林市'],
            ['421001198301097785', '荆州市'],
            ['141102198906264202', '吕梁市'],
            ['210224199007216341', '大连市'],
            ['653024197412039566', '克孜勒苏柯尔克孜自治州'],
            ['371581198203163691', '聊城市'],
            ['510623197307181694', '德阳市'],
        ];
    }

    /**
     * @dataProvider cityProvider
     */
    public function testCity($id, $city)
    {
        $identity = $this->testMake($id);
        $this->assertEquals($city, $identity->city());
    }

    public function countyProvider()
    {
        return [

            ['45032619840627183x', '永福县'],
            ['371581198203163691', '临清市'],
            ['510623197307181694', '中江县'],
            ['210204197008045252', '沙河口区'],
            ['150203199512020472', '昆都仑区'],
            ['653024197412039566', '乌恰县'],
            ['411024198208300980', '鄢陵县'],
            ['43312319791130094X', '凤凰县'],
            ['130825199105138665', '隆化县'],
            ['210224199007216341', '长海县'],
            ['141102198906264202', '离石区']
        ];
    }

    /**
     * @dataProvider countyProvider
     */
    public function testCounty($id, $county)
    {
        $identity = $this->testMake($id);
        $this->assertEquals($county, $identity->county());
    }

    public function ZodiacProvider()
    {
        return [
            ['45032619840627183x', '巨蟹座'],
            ['371581198203163691', '双鱼座'],
            ['510623197307181694', '巨蟹座'],
            ['210204197008045252', '狮子座'],
            ['150203199512020472', '射手座'],
            ['653024197412039566', '射手座'],
            ['411024198208300980', '处女座'],
            ['43312319791130094X', '射手座'],
            ['130825199105138665', '金牛座'],
            ['210224199007216341', '巨蟹座'],
            ['141102198906264202', '巨蟹座']
        ];
    }

    /**
     * @dataProvider ZodiacProvider
     */
    public function testZodiac($id, $h)
    {
        $identity = $this->testMake($id);
        $this->assertEquals($h, $identity->zodiac());
    }
}
