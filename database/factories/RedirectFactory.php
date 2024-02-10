<?php

namespace Database\Factories;

use App\Models\Redirect;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Redirect>
 */
class RedirectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Redirect::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'url' => fake()->url()
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Redirect $redirect) {
            $redirect->code = Hashids::connection('main')->encode($redirect->id);
            $redirect->save();
        });
    }
}
