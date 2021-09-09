<?php

namespace Bryceandy\Selcom\Traits;

use Bryceandy\Selcom\Exceptions\MissingDataException;

trait ValidatesData
{
    public function validateCheckoutData($data)
    {
        $this->validate(
            ['email', 'name', 'phone', 'amount', 'is_ussd', 'transaction_id'],
            $data
        );
    }

    /**
     * @throws MissingDataException
     */
    private function validate($keys, $submittedData)
    {
        $missing = collect($keys)
            ->diff(collect(array_keys($submittedData)));

        if ($missing->count()) {
            throw new MissingDataException(
                "The following key is missing from your data: {$missing->first()}"
            );
        }
    }
}