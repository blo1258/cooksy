<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User; // Kendi User varlığınızın yolunu buraya ekleyin

class IsNewPasswordDifferentValidator extends ConstraintValidator
{
    private $hasher;
    private $security;

    // PasswordHasher ve mevcut kullanıcıyı almak için Security servisini enjekte ediyoruz.
    public function __construct(UserPasswordHasherInterface $hasher, Security $security)
    {
        $this->hasher = $hasher;
        $this->security = $security;
    }

    public function validate($newPlainPassword, Constraint $constraint): void
    {
        /* @var $constraint \App\Validator\IsNewPasswordDifferent */

        if (null === $newPlainPassword || '' === $newPlainPassword) {
            // Şifre boşsa, diğerNotBlank kısıtlamaları halleder.
            return;
        }

        // Giriş yapan mevcut kullanıcı nesnesini alır
        $user = $this->security->getUser();

        // Güvenlik kontrolü (sadece giriş yapmış kullanıcılar için geçerli)
        if (!$user instanceof User) {
            return;
        }

        // Eğer yeni girilen DÜZ şifre, mevcut kullanıcının KAYITLI (hashed) şifresiyle eşleşiyorsa,
        // bu, yeni şifrenin eskiyle aynı olduğu anlamına gelir.
        if ($this->hasher->isPasswordValid($user, $newPlainPassword)) {
            
            // Doğrulama hatası oluştur.
            $this->context->buildViolation($constraint->message)
                // Hatanın formdaki 'Nouveau mot de passe' alanında görünmesini sağlar
                ->atPath('newPassword.first') 
                ->addViolation();
        }
    }
}
