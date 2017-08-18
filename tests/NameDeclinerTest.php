<?php declare(strict_types=1);

use Madyanov\NameDecliner;
use PHPUnit\Framework\TestCase;

class NameDeclinerTest extends TestCase
{
    public function testRegression()
    {
        // uncomment for test data regeneration
        // $this->regenerateTestData();

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

    private function regenerateTestData()
    {
        $result = ['f' => [], 'm' => []];

        foreach (['f', 'm'] as $gender) {
            $names = file('tests/data/' . $gender . '_names.txt');

            foreach ($names as $key => $name) {
                $decliner = new NameDecliner(trim($name));

                if ($gender === 'f') {
                    $result[$gender][$key] = $decliner->applyFemaleNameRules();
                } else if ($gender === 'm') {
                    $result[$gender][$key] = $decliner->applyMaleNameRules();
                }
            }
        }

        file_put_contents('tests/data/valid.json', json_encode($result, JSON_UNESCAPED_UNICODE));
    }
}