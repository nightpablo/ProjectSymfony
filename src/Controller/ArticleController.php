<?php
namespace App\Controller;

use App\Entity\Article;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Doctrine\ORM\EntityRepository;


class ArticleController extends Controller
{
    /**
     * @Route("/", name="searching")
     * 
     */
    public function search(Request $request){
        $article = new Article();

        $form = $this->createFormBuilder($article)
                ->add('title',TextType::class,array('required'=>false,'attr'=>array('class' => 'form-control')))
                ->add('body',TextareaType::class,array('required'=>false,'attr'=>array('class' => 'form-control')))
                ->getForm();
                
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid() && $request->request->has('CreateNew')){
            //'CreateNew' === $request->getClickedButton()->getName()){
            return $this->redirectToRoute("new_article");
        }
        else if($form->isSubmitted() && $form->isValid() && $request->request->has('SearchByFilter')){
            //'SearchByFilter' === $request->getClickedButton()->getName()){
            
            $article = $form->getData();
            
            
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery(
                'SELECT a
                FROM App:Article a
                WHERE a.title like :title
                and a.body like :body
                or a.body IS NULL'
            )->setParameter('title', "%".$article->getTitle()."%")
            ->setParameter('body', "%".$article->getBody()."%");

            $articles = $query->getResult();
            
            return $this->render('articles/search.html.twig', array('articles' => $articles,'form'=>$form->createView()));
            //return $this->render('articles/index.html.twig', array('articles' => $articles));
        } 
        return $this->render('articles/search.html.twig', array('articles'=>null,'form'=>$form->createView()));
    }

    // /**
    //  * @Route("/article/{id}", name="article_show")
    //  */
    // public function show($id)
    // {
        
    //     $article = $this->getDoctrine()->getRepository(Article::class)->find($id);

    //     return $this->render('articles/show.html.twig', array('article' => $article));
    // }

    /**
     * @Route("/article/delete/{id}")
     * @Method({"DELETE"})
     */
    public function delete(Request $request, $id){
        $article= $this->getDoctrine()->getRepository(Article::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($article);
        $entityManager->flush();

        $response = new Response();
        $response->send();
    }



    /**
     * @Route("/article/new", name="new_article")
     * Method({"GET","POST"})
     */
    public function new(Request $request){
        $article = new Article();
            
        $form = $this->createFormBuilder($article)
                ->add('title',TextType::class,array('attr'=>array('class' => 'form-control')))
                ->add('body',TextareaType::class,array('required'=>false,'attr'=>array('class' => 'form-control')))
                ->add('save',SubmitType::class,array('label'=>'Create','attr'=>array('class' => 'btn btn-primary mt-3')))
                ->getForm();


        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $article= $form->getData();
            $entityManager= $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('searching');
        } 
        return $this->render('articles/new.html.twig', array('form'=>$form->createView()));

    }

    /**
     * @Route("/article/edit/{id}", name="edit_article")
     * Method({"GET","POST"})
     */
    public function edit(Request $request,$id){
        
        $article= $this->getDoctrine()->getRepository(Article::class)->find($id);
        
        $form = $this->createFormBuilder($article)
                ->add('title',TextType::class,array('attr'=>array('class' => 'form-control')))
                ->add('body',TextareaType::class,array('required'=>false,'attr'=>array('class' => 'form-control')))
                ->add('save',SubmitType::class,array('label'=>'Update','attr'=>array('class' => 'btn btn-primary mt-3')))
                ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            
            $entityManager= $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('articles_list');
        } 
        return $this->render('articles/edit.html.twig', array('form'=>$form->createView()));

    }

    /**
     * @Route("/article/save")
     */

    public function save(){
        $entityManager = $this->getDoctrine()->getManager();

        $article = new Article();
        $article->setTitle('Article One');
        $article->setBody('this is body');
        $entityManager->persist($article);

        $entityManager->flush();

        return new Response('Saves an article with the id of '.$article->getId());
     }
}
 