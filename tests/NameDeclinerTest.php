<?php declare(strict_types=1);

use Madyanov\NameDecliner;
use PHPUnit\Framework\TestCase;

class NameDeclinerTest extends TestCase
{
    public function testRegression()
    {
        $valid = json_decode(file_get_contents('tests/data/valid.json'), true);

        foreach (['f', 'm'] as $gender) {
            $names = file('tests/data/' . $gender . '_names.txt');

            foreach ($names as $key => $name) {
                $decliner = new NameDecliner(trim($name));
                $declined = [];

                if ($gender === 'f') {
                    $declined = $decliner->applyFemaleNameRules();
                } else if ($gender === 'm') {
                    $declined = $decliner->applyMaleNameRules();
                }

                foreach ($declined as $case => $currentString) {
                    $validString = $valid[$gender][$key][$case];
                    $this->assertEquals($validString, $currentString, "gender $gender, case $case");
                }
            }
        }
    }
}