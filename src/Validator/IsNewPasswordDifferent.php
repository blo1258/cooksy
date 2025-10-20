<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @NamedArgumentConstructor
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class IsNewPasswordDifferent extends Constraint
{
    // Hata mesajı 
    public string $message = 'Votre nouveau mot de passe ne peut pas être le même que votre ancien mot de passe.';

    // Bu kısıtlama, sadece bir özelliğe uygulanabilir (örn. newPassword)
    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}