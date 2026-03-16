<?php

namespace Kompo\Tests\Unit\Element;

use Kompo\Core\KompoId;
use Kompo\Input;
use Kompo\Tests\EnvironmentBoot;
use Kompo\Tests\Utilities\_Form;

class KompoIdTest extends EnvironmentBoot
{
    /** @test */
    public function kompo_id_is_correctly_created_on_elements()
    {
        $el = Input::form('<span>Some Label()*</span>&#');

        $kompoId = KompoId::getFromElement($el);
        $this->assertNotNull($kompoId);
        $this->assertEquals('SomeLabel', substr($kompoId, 0, 9));

        $el = Input::form('<span>Some Label()*</span>&#');

        $kompoId2 = KompoId::getFromElement($el);
        $this->assertNotNull($kompoId2);
        $this->assertEquals('SomeLabel', substr($kompoId2, 0, 9));
        $this->assertFalse($kompoId == $kompoId2); //testing uniqid() generation
    }

    /** @test */
    public function kompo_id_is_correctly_created_on_komponents_without_id()
    {
        $form = _Form::boot();
        $kompoId = KompoId::getFromElement($form);
        $this->assertNotNull($kompoId);
        $this->assertEquals('_Form', $kompoId);

        $form = _Form::boot();
        $kompoId2 = KompoId::getFromElement($form);
        $this->assertNotNull($kompoId2);
        $this->assertEquals('_Form', $kompoId2);

        // Same class = same stable KompoId (deterministic, no uniqid())
        $this->assertEquals($kompoId, $kompoId2);
    }

    /** @test */
    public function kompo_id_is_correctly_created_on_komponents_with_id()
    {
        $form = _SetElementIdForm::boot();

        $kompoId = KompoId::getFromElement($form);
        $this->assertNotNull($kompoId);
        $this->assertEquals($kompoId, 'form-id');
    }
}
