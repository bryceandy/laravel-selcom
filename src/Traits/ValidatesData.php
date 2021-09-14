<?php

namespace Bryceandy\Selcom\Traits;

use Illuminate\Support\Arr;
use Bryceandy\Selcom\Exceptions\{
    InvalidDataException,
    MissingDataException,
};

trait ValidatesData
{
    /**
     * @throws InvalidDataException
     * @throws MissingDataException
     */
    public function validateCheckoutData($data)
    {
        $this->validate($this->getMinimalOrderKeys(), $data);
    }

    /**
     * @throws InvalidDataException
     * @throws MissingDataException
     */
    public function validateCardCheckoutData($data)
    {
        if (($data['no_redirection'] ?? false) && ! Arr::has($data, ['user_id'])) {
            throw new InvalidDataException(
                'You are missing the following: user_id. Otherwise, set no_redirection to false'
            );
        }

        $this->validate(
            array_merge($this->getMinimalOrderKeys(), ['address', 'postcode']),
            $data
        );
    }

    private function getMinimalOrderKeys(): array
    {
        return [
            'name',
            'email',
            'phone',
            'amount',
            'transaction_id',
        ];
    }

    /**
     * @throws InvalidDataException
     * @throws MissingDataException
     */
    private function validate($keys, $submittedData)
    {
        $missing = collect($keys)
            ->diff(collect(array_keys($submittedData)));

        if ($missing->count()) {
            throw new MissingDataException(
                "The following keys are missing from your data: {$missing->implode(', ')}"
            );
        }

        if (isset($submittedData['name']) &&
            count(explode(' ', $submittedData['name'])) < 2)
        {
            throw new InvalidDataException('Name must contain at-least 2 words');
        }
    }
}