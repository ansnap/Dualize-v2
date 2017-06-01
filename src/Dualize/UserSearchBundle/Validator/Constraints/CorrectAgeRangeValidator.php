<?php

namespace Dualize\UserSearchBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CorrectAgeRangeValidator extends ConstraintValidator
{

	public function validate($protocol, Constraint $constraint)
	{
		if ($protocol->getAgeFrom() && $protocol->getAgeTo() && $protocol->getAgeFrom() > $protocol->getAgeTo()) {
			$this->context->addViolationAt('ageTo', $constraint->message, array(), null);
		}
	}

}
