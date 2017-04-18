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
            'action' => $this->generateUrl('calendar-note-category-add')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getCalendarNoteCategoryRepository()->save($noteCategory);

            return $this->redirectToRoute('calendar-note-category-edit', [
                'id' => $noteCategory->getId()
            ]);
        }

        return $this->render('my/calendar/form-note-category.html.twig', [
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

        $form = $this->createForm(Form\CalendarNoteCategoryType::class, $noteCategory ,[
            'action' => $this->generateUrl('calendar-note-category-edit', ['id' => $noteCategory->getId()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getCalendarNoteCategoryRepository()->save($noteCategory);

            return $this->redirectToRoute('calendar-note-category-edit', [
                'id' => $noteCategory->getId()
            ]);
        }
        return $this->render('my/calendar/form-note-category.html.twig', [
            'form' => $form->createView(),
            'categoryNotes' => $this->getCalendarNoteRepository()->findByCategory($noteCategory)
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

            return $this->redirectToRoute('calendar-note-category-edit', ['id' => $noteCategory->getId()]);
        }

        if ($noteCategory->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }

        if ($noteCategory->getInternalId() !== null) {
            $this->addFlash('error', $this->get('translator')->trans('This category cannot be deleted.'));
        }

        $this->getCalendarNoteCategoryRepository()->remove($noteCategory);
        $this->addFlash('success', $this->get('translator')->trans('The category has been deleted.'));

        return $this->redirectToRoute('calendar-note-manage');
    }

    /**
     * @Route("/note/add", name="calendar-note-add")
     */
    public function noteAddAction(Request $request, Account $account)
    {
        $calendarNote = new CalendarNote();
        $calendarNote->setAccount($account);
        if ($request->get('categoryId')) {
            $calendarNote->setCategory($this->getDoctrine()->getManager()->getReference('CoreBundle:CalendarNoteCategory', $request->get('categoryId')));
        }

        $form = $this->createForm(Form\CalendarNoteType::class, $calendarNote,[
            'action' => $this->generateUrl('calendar-note-add')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getCalendarNoteRepository()->save($calendarNote);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_DATA_BROWSER);
            $this->addFlash('notice', $this->get('translator')->trans('Notice has been added.'));
            return $this->redirectToRoute('calendar-note-manage');
        }

        return $this->render('my/calendar/note-form.html.twig', [
            'form' => $form->createView(),
            'category_id' => $calendarNote->getId()
        ]);
    }

    /**
     * @Route("/note/manage", name="calendar-note-manage")
     */
    public function noteManageAction(Request $request, Account $account)
    {
        $calendarNote = new CalendarNote();
        $calendarNote->setAccount($account);

        $form = $this->createForm(Form\CalendarNoteType::class, $calendarNote,[
            'action' => $this->generateUrl('calendar-note-add')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getCalendarNoteRepository()->save($calendarNote);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_DATA_BROWSER);

            return $this->redirectToRoute('calendar-note-edit', [
                'id' => $calendarNote->getId()
            ]);
        }

        return $this->render('my/calendar/note-manage.html.twig', [
            'form' => $form->createView(),
            'category_id' => $calendarNote->getId(),
            'noteCategories' => $this->getCalendarNoteCategoryRepository()->findAllFor($account),
            'notes' => $this->getCalendarNoteRepository()->findAllFor($account, 10)
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
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_DATA_BROWSER);

            return $this->redirectToRoute('calendar-note-category-edit', [
                'id' => $calendarNote->getCategory()->getId()
            ]);
        }

        return $this->render('my/calendar/note-form.html.twig', [
            'form' => $form->createView(),
            'category_id' => $calendarNote->getId()
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
        $this->addFlash('success', $this->get('translator')->trans('The note has been deleted.'));

        return $this->redirectToRoute('calendar-note-category-edit', ['id' => $calendarNote->getCategory()->getId()]);
    }
}
