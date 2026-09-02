<?php

namespace App\Tests\Form;

use App\Form\ChangerMotDePasseType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Vérifie que le contrôle de complexité du mot de passe rejette bien
 * les mots de passe non conformes et accepte un mot de passe robuste.
 */
class ChangerMotDePasseTypeTest extends TypeTestCase
{
    public function testRejetteUnMotDePasseTropCourt(): void
    {
        $form = $this->factory->create(ChangerMotDePasseType::class);
        $form->submit([
            'motDePasseActuel' => 'ancien',
            'nouveauMotDePasse' => ['first' => 'Ab1!', 'second' => 'Ab1!'],
        ]);

        $this->assertFalse($form->get('nouveauMotDePasse')->isValid());
    }

    public function testRejetteUnMotDePasseSansCaractereSpecial(): void
    {
        $form = $this->factory->create(ChangerMotDePasseType::class);
        $form->submit([
            'motDePasseActuel' => 'ancien',
            'nouveauMotDePasse' => ['first' => 'MotDePasse1234', 'second' => 'MotDePasse1234'],
        ]);

        $this->assertFalse($form->get('nouveauMotDePasse')->isValid());
    }

    public function testRejetteUnMotDePasseSansMajuscule(): void
    {
        $form = $this->factory->create(ChangerMotDePasseType::class);
        $form->submit([
            'motDePasseActuel' => 'ancien',
            'nouveauMotDePasse' => ['first' => 'motdepasse1234!', 'second' => 'motdepasse1234!'],
        ]);

        $this->assertFalse($form->get('nouveauMotDePasse')->isValid());
    }

    public function testRejetteDeuxMotsDePasseDifferents(): void
    {
        $form = $this->factory->create(ChangerMotDePasseType::class);
        $form->submit([
            'motDePasseActuel' => 'ancien',
            'nouveauMotDePasse' => ['first' => 'MotDePasse1234!', 'second' => 'Different1234!'],
        ]);

        $this->assertFalse($form->get('nouveauMotDePasse')->isValid());
    }

    public function testAccepteUnMotDePasseRobuste(): void
    {
        $form = $this->factory->create(ChangerMotDePasseType::class);
        $form->submit([
            'motDePasseActuel' => 'ancien',
            'nouveauMotDePasse' => ['first' => 'Tr0ub4dor&Zebre!', 'second' => 'Tr0ub4dor&Zebre!'],
        ]);

        $this->assertTrue($form->get('nouveauMotDePasse')->isValid());
    }
}
