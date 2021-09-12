<?php

namespace Bryceandy\Selcom\Traits;

use Bryceandy\Selcom\Exceptions\{
    InvalidDataException,
    MissingDataException,
};

trait ValidatesData
{
    public function validateCheckoutData($data)
    {
        $this->validate($this->getMinimalOrderKeys(), $data);
    }

    public function validateCardCheckoutData($data)
    {
        $this->validate(
            array_merge($this->getMinimalOrderKeys(), [
                'address',
                'postcode',
                'buyer_uuid',
                'user_id',
            ]),
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