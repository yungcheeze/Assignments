<?php
class location
{
    private $latRadians;
    private $longRadians;
    private $latLong;

    private  static $conv = 0.01745329251; //pi/180
    private static $radius = 3959; //earths radius in miles

    public function __construct($latLng)
    {
        $this->setLatLong($latLng);
    }

    public function distanceFrom($other)
    {
        $dLat = $other->getLatRadians() - $this->getLatRadians();
        $dLong = $other->getLongRadians() - $this->getLongRadians();

        $a = pow(sin($dLat/2), 2) + cos($this->getLatRadians()) * cos($other->getLatRadians()) * pow(sin($dLong/2), 2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $d = location::$radius * $c;
        return $d;
    }

    public function getLatRadians()
    {
        return $this->latRadians;
    }

    public function setLatRadians($latRadians)
    {
        $this->latRadians = $latRadians;
    }

    public function getLongRadians()
    {
        return $this->longRadians;
    }

    public function setLongRadians($longRadians)
    {
        $this->longRadians = $longRadians;
    }

    public function getLatLong()
    {
        return $this->latLong;
    }

    public function setLatLong($latLng)
    {
        $arr = explode(',', preg_replace("/[^0-9,.-]/", "", $latLng));
        $this->latRadians = floatval($arr[0]) * location::$conv;
        $this->longRadians = floatval($arr[1]) * location::$conv;
        $this->latLong = $latLng;
    }
}