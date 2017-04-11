<?php

namespace Runalyze\Bundle\CoreBundle\Controller\My;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\CalendarNote;
use Runalyze\Bundle\CoreBundle\Entity\CalendarNoteCategory;
use Runalyze\Bundle\CoreBundle\Form;
use Runalyze\Bundle\CoreBundle\Services\AutomaticReloadFlagSetter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/my/calendar")
 * @Security("has_role('ROLE_USER')")
 */
class CalendarNotesController extends Controller
{
    /**
     * @return CalendarNoteRepository
     */
    protected function getCalendarNoteRepository()
    {
        return $this->getDoctrine()->getRepository('CoreBundle:CalendarNote');
    }

    /**
     * @return CalendarNoteCategoryRepository
     */
    protected function getCalendarNoteCategoryRepository()
    {
        return $this->getDoctrine()->getRepository('CoreBundle:CalendarNoteCategory');
    }

    /**
     * @Route("/overview", name="calendar-category-overview")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function overviewAction(Account $account)
    {
        return $this->render('my/calendar/categoryOverview.html.twig', [
            'calendarNoteCategories' => $this->getCalendarNoteCategoryRepository()->findAllFor($account)
        ]);
    }

    /**
     * @Route("/note/category/add", name="calendar-note-category-add")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function categoryAddAction(Request $request, Account $account)
    {
        $noteCategory = new CalendarNoteCategory();
        $noteCategory->setAccount($account);

        $form = $this->createForm(Form\CalendarNoteCategoryType::class, $noteCategory ,[
            'action' => $this->generateUrl('equipment-category-add')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getCalendarNoteCategoryRepository()->save($noteCategory);

            return $this->redirectToRoute('calendar-note-category-edit', [
                'id' => $noteCategory->getId()
            ]);
        }

        return $this->render('my/equipment/form-category.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/note/category/{id}/edit", name="calendar-note-category-edit")
     * @ParamConverter("noteCategory", class="CoreBundle:CalendarNoteCategory")
     *
     * @param Request $request
     * @param CalendarNoteCategory $noteCategory
     * @param Account $account
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function categoryEditAction(Request $request, CalendarNoteCategory $noteCategory, Account $account)
    {
        if ($noteCategory->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(Form\EquipmentCategoryType::class, $noteCategory ,[
            'action' => $this->generateUrl('equipment-category-edit', ['id' => $noteCategory->getId()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getCalendarNoteCategoryRepository()->save($noteCategory);

            return $this->redirectToRoute('equipment-category-edit', [
                'id' => $noteCategory->getId()
            ]);
        }

        return $this->render('my/equipment/form-category.html.twig', [
            'form' => $form->createView(),
            'equipment' => $this->getCalendarNoteCategoryRepository()->findByTypeId($noteCategory->getId(), $account)
        ]);
    }

    /**
     * @Route("/note/category/{id}/delete", name="calendar-note-category-delete")
     * @ParamConverter("noteCategory", class="CoreBundle:CalendarNoteCategory")
     */
    public function deleteCategoryAction(Request $request, CalendarNoteCategory $noteCategory, Account $account)
    {
        if (!$this->isCsrfTokenValid('deleteCalendarNoteCategory', $request->get('t'))) {
            $this->addFlash('error', $this->get('translator')->trans('Invalid token.'));

            return $this->redirectToRoute('equipment-overview');
        }

        if ($noteCategory->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }

        $this->getCalendarNoteCategoryRepository()->remove($noteCategory);
        $this->addFlash('success', $this->get('translator')->trans('The category has been deleted.'));

        return $this->redirectToRoute('equipment-overview');
    }

    /**
     * @Route("/note/{categoryId}/add/", name="calendar-note-add", requirements={"categoryId" = "\d+"})
     * @ParamConverter("calendarNoteCategory", class="CoreBundle:CalendarNote")
     */
    public function noteAddAction(Request $request, CalendarNote $calendarNoteCategory, Account $account)
    {
        $note = new CalendarNote();
        $note->setAccount($account);
        $note->setCategory($calendarNoteCategory);

        $form = $this->createForm(Form\EquipmentType::class, $note,[
            'action' => $this->generateUrl('calendar-note-add', ['categoryId' => $note->getCategory()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getCalendarNoteRepository()->save($calendarNote);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_PLUGINS);

            return $this->redirectToRoute('calendar-note-edit', [
                'id' => $calendarNote->getCategory()->getId()
            ]);
        }

        return $this->render('my/equipment/form-equipment.html.twig', [
            'form' => $form->createView(),
            'category_id' => $calendarNote->getId()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="calendar-note-edit")
     * @ParamConverter("calendarNote", class="CoreBundle:CalendarNote")
     * @param Request $request
     * @param CalendarNote $calendarNote
     * @param Account $account
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function noteEditAction(Request $request, CalendarNote $calendarNote, Account $account)
    {
        if ($calendarNote->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(Form\CalendarNoteType::class, $calendarNote,[
            'action' => $this->generateUrl('calendar-note-edit', ['id' => $calendarNote->getId()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getCalendarNoteRepository()->save($calendarNote);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_PLUGINS);

            return $this->redirectToRoute('equipment-category-edit', [
                'id' => $calendarNote->getType()->getId()
            ]);
        }

        return $this->render('my/equipment/form-equipment.html.twig', [
            'form' => $form->createView(),
            'category_id' => $calendarNote->getType()->getId()
        ]);
    }

    /**
     * @Route("/{id}/delete", name="calendar-note-delete")
     * @ParamConverter("calendarNote", class="CoreBundle:CalendarNote")
     */
    public function deleteEquipmentAction(Request $request, CalendarNote $calendarNote, Account $account)
    {
        if (!$this->isCsrfTokenValid('deleteCalendarNote', $request->get('t'))) {
            $this->addFlash('error', $this->get('translator')->trans('Invalid token.'));

            return $this->redirectToRoute('equipment-category-edit', [
                'id' => $calendarNote->getType()->getId()
            ]);
        }

        if ($calendarNote->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }

        $this->getCalendarNoteRepository()->remove($calendarNote);
        $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_PLUGINS);
        $this->addFlash('success', $this->get('translator')->trans('The object has been deleted.'));

        //close
    }
}
