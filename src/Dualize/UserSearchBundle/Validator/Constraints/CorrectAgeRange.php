<?php

namespace Dualize\UserSearchBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CorrectAgeRange extends Constraint
{

	public $message = 'Неверно задан возрастной диапазон.';

	public function validatedBy()
	{
		return get_class($this) . 'Validator';
	}

	public function getTargets()
	{
		return self::CLASS_CONSTRAINT;
	}

}
