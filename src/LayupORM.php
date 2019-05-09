<?php
namespace Layup;

interface LayupORM {
    public function __init();
    public function __save();
    public function __update();
    public function __toObject(array $arr);
    public function save();
    public function toJson();
    public static function find(string $_id);
}