<?php


namespace Tests\Traits;


use Faker\Factory;

/**
 * Trait MakesFakeIssuesTrait
 * @package Tests\Traits
 */
trait MakesFakeIssues
{
    /**
     * @return array
     */
    protected function makeFakeIssue()
    {
        $faker = Factory::create('ru_RU');
        return [
            'issue' => [
                'id' => $faker->randomNumber(2),
                'project' => [
                    'id' => $faker->randomNumber(2),
                ],
                "subject" => $faker->realText(60),
                "priority_id" => 4,
                "created_on" => $faker->dateTime()->format('Y-m-d\TH:i:s\Z')
            ]
        ];
    }
}