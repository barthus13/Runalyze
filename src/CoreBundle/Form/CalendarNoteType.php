<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\CalendarNoteCategory;
use Runalyze\Bundle\CoreBundle\Form\Type\DurationNullableType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Runalyze\Profile\Calendar;

class CalendarNoteType extends AbstractType
{
    /** @var TokenStorage */
    protected $TokenStorage;

    public function __construct(TokenStorage $tokenStorage)
    {
        $this->TokenStorage = $tokenStorage;
    }

    /**
     * @return Account
     */
    protected function getAccount()
    {
        $account = $this->TokenStorage->getToken() ? $this->TokenStorage->getToken()->getUser() : null;

        if (!($account instanceof Account)) {
            throw new \RuntimeException('Calendar note must have a valid account token.');
        }

        return $account;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('note', TextareaType::class, array(
                'label' => 'Note',
                'required' => true,
                'attr' => array(
                    'autofocus' => true,
                    'class' => 'full-size'
                )
            ))
            ->add('startDate', DateType::class, [
                'label' => 'Start date',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'html5' => false,
                'attr' => ['class' => 'pick-a-date small-size', 'placeholder' => 'dd.mm.YYYY']
            ])
            ->add('endDate', DateType::class, [
                'label' => 'End date',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'html5' => false,
                'attr' => ['class' => 'pick-a-date small-size', 'placeholder' => 'dd.mm.YYYY']
            ])
            ->add('category', ChoiceType::class, [
                'required' => true,
                'choices' => $this->getAllNoteCategories(),
                'choice_label' => 'name',
                'label' => 'Note category',
                'placeholder' => 'Choose category'
            ])
        ;
    }

    private function getAllNoteCategories() {
        $calendarNoteCategories = $this->getAccount()->getCalendarNoteCategories();
        $internalCategories = [Calendar\CategoryProfile::ILLNESS, Calendar\CategoryProfile::INJURY];
        if (!empty($calendarNoteCategories)) {
            foreach ($calendarNoteCategories as $category) {
                    unset($internalCategories[array_search($category->getInternalId(), $internalCategories)]);
            }
        }

        foreach ($internalCategories as $internalCategoryId) {
            $calendarCategory = Calendar\CategoryProfile::objectFor($internalCategoryId);
            $calendarCategory->setAccount($this->getAccount());
            $calendarNoteCategories[] = $calendarCategory;
        }
        return $calendarNoteCategories;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Runalyze\Bundle\CoreBundle\Entity\CalendarNote'
        ));
    }
}
