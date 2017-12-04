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
     * @param array $attributes
     * @return array
     */
    protected function makeFakeIssue($attributes = [])
    {
        $faker = Factory::create('ru_RU');
        $issue = array_merge([
            'issue' => [
                'id' => $faker->unique()->randomNumber(5),
                'project' => [
                    'id' => 90,
                    'name' => 'Служба технической поддержки МЗ Арсенал'
                ],
                'tracker' => [
                    'id' => 3,
                    'name' => 'Поддержка'
                ],
                'status' => [
                    'id' => 2,
                    'name' => 'Назначен'
                ],
                'priority' => [
                    'id' => 4,
                    'name' => 'Нормальный'
                ],
                'author' => [
                    'id' => $faker->randomNumber(3),
                    'name' => $faker->name
                ],
                'assigned_to' => [
                    'id' => $faker->randomNumber(3),
                    'name' => $faker->name
                ],
                'subject' => $faker->name . ' : ' . $faker->realText(60),
                'description' => $faker->realText(),
                'start_date' => $faker->dateTimeThisMonth()->format('Y-m-d'),
                'done_ratio' => $faker->numberBetween(0, 100),
                'custom_fields' => [
                    [
                        'id' => 1,
                        'name' => 'Подразделение',
                        'value' => '115 Управление информационных систем'
                    ],
                    [
                        'id' => 65,
                        'name' => 'Сервис',
                        'value' => 'Организация рабочих мест пользователей'
                    ]
                ],
                'created_on' => $faker->dateTimeThisMonth()->format('Y-m-d\TH:i:s\Z'),
                'updated_on' => $faker->dateTimeThisMonth()->format('Y-m-d\TH:i:s\Z'),
            ]
        ], $attributes);
        return $issue;
    }
}