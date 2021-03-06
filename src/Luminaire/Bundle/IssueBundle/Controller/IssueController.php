<?php

namespace Luminaire\Bundle\IssueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Luminaire\Bundle\IssueBundle\Form\Type\IssueType;
use Luminaire\Bundle\IssueBundle\Entity\Issue;
use Luminaire\Bundle\IssueBundle\Entity\IssueType as IssueTypeEntity;
use Luminaire\Bundle\IssueBundle\Entity\Repository\IssueRepository;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @Route("/issue")
 */
class IssueController extends Controller
{
    /**
     * @Route("/", name="luminaire_issue_index")
     * @Template
     * @AclAncestor("luminaire_issue_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'Luminaire\Bundle\IssueBundle\Entity\Issue'];
    }

    /**
     * @param Request $request
     *
     * @Route("/create", name="luminaire_issue_create")
     * @Template("LuminaireIssueBundle:Issue:update.html.twig")
     * @AclAncestor("luminaire_issue_view")
     *
     * @return array|RedirectResponse
     */
    public function createAction(Request $request)
    {
        $issue = new Issue();
        $parentId = $request->get('parent_id');
        if ($parentId) {
            $parent = $this->getIssueRepository()->findOneBy([
                'id'   => $parentId,
                'type' => IssueTypeEntity::TYPE_STORY,
            ]);
            if ($parent) {
                $issue->setParent($parent);
                $issue->setType($this->getIssueType());
            }
        }
        return $this->update($issue);
    }

    /**
     * @return IssueTypeEntity
     */
    protected function getIssueType()
    {
        return $this->getIssueTypeRepository()->findOneBy(['name' => IssueTypeEntity::TYPE_SUBTASK]);
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getIssueTypeRepository()
    {
        $issueTypeClass = 'LuminaireIssueBundle:IssueType';
        return $this->getDoctrine()->getManagerForClass($issueTypeClass)->getRepository($issueTypeClass);
    }

    /**
     * @return IssueRepository
     */
    protected function getIssueRepository()
    {
        $issueTypeClass = 'LuminaireIssueBundle:Issue';
        return $this->getDoctrine()->getManagerForClass($issueTypeClass)->getRepository($issueTypeClass);
    }

    /**
     * @param Issue $entity
     *
     * @Route("/update/{id}", name="luminaire_issue_update", requirements={"id":"\d+"})
     * @Template()
     * @AclAncestor("luminaire_issue_update")
     *
     * @return array|RedirectResponse
     */
    public function updateAction(Issue $entity)
    {
        return $this->update($entity);
    }

    /**
     * @param Issue $entity
     *
     * @return array|RedirectResponse
     */
    protected function update(Issue $entity)
    {
        $form = $this->createForm(IssueType::NAME, $entity);

        return $this->get('oro_form.model.update_handler')->handleUpdate(
            $entity,
            $form,
            function (Issue $entity) {
                return [
                    'route'      => 'luminaire_issue_update',
                    'parameters' => ['id' => $entity->getId()],
                ];
            },
            function (Issue $entity) {
                return [
                    'route'      => 'luminaire_issue_show',
                    'parameters' => ['id' => $entity->getId()],
                ];
            },
            $this->get('translator')->trans('luminaire.issue.controller.issue.saved.message'),
            $this->get('luminaire_issue.form.handler.entity')->setForm($form)
        );
    }

    /**
     * @param Issue $entity
     *
     * @Route("/{id}", name="luminaire_issue_show", requirements={"id"="\d+"})
     * @Template
     *
     * @return array
     */
    public function showAction(Issue $entity)
    {
        return ['entity' => $entity];
    }
}
