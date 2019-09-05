<?php
// src/Controller/PagesController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use App\Entity\Content;
use App\Entity\Document;
use App\Entity\Article;
use App\Entity\Event;
use App\Entity\Partner;
use App\Entity\Person;
use App\Entity\Admin;

class PagesController extends AbstractController
{

    public function getContentArray(Request $request){
        
        $locale = $request->getLocale();
        $crepository = $this->getDoctrine()->getRepository(Content::class);
        $allContent = $crepository->findAll();
        $content = array();
        for($i = 0; $i<sizeof($allContent); $i++){
            if( $locale=='lv' ) $content[ $allContent[$i]->getId() ] = $allContent[$i]->getContent();
            if( $locale=='en' ) $content[ $allContent[$i]->getId() ] = $allContent[$i]->getContenten();
            if( $locale=='ru' ) $content[ $allContent[$i]->getId() ] = $allContent[$i]->getContentru();
        } 
        return $content;
    }

    public function dateToTimeElapsed($request, \DateTimeInterface $date){
        $locale = $request->getLocale();
        //$interval = $date->diff( new DateTime('now') );
        $now = date_create();
        $interval = date_diff($date,$now);
        if( $interval->y == 1 ){
            if( $locale=='lv' ) return $interval->format('Pirms gada');
            if( $locale=='en' ) return $interval->format('%y year ago');
            if( $locale=='ru' ) return $interval->format('%y год назад');
        }
        if( $interval->y > 1 ){
            if( $locale=='lv' ) return $interval->format('Pirms %y gadiem');
            if( $locale=='en' ) return $interval->format('%y years ago');
            if( $locale=='ru' ) return $interval->format('%y года назад');
        }
        if( $interval->m == 1 ){
            if( $locale=='lv' ) return $interval->format('Pirms mēneša');
            if( $locale=='en' ) return $interval->format('%m month ago');
            if( $locale=='ru' ) return $interval->format('%m месяц назад');
        }
        if( $interval->m > 1 ){
            if( $locale=='lv' ) return $interval->format('Pirms %m mēnešiem');
            if( $locale=='en' ) return $interval->format('%m months ago');
            if( $locale=='ru' ) return $interval->format('%m мес. назад');
        }
        if( $interval->d == 1 ){
            if( $locale=='lv' ) return $interval->format('Pirms %d dienas');
            if( $locale=='en' ) return $interval->format('%d day ago');
            if( $locale=='ru' ) return $interval->format('%d день назад');
        }
        if( $interval->d > 1 ){
            if( $locale=='lv' ) return $interval->format('Pirms %d dienām');
            if( $locale=='en' ) return $interval->format('%d days ago');
            if( $locale=='ru' ) return $interval->format('%d д. назад');
        }
        if( $interval->h == 1 ){
            if( $locale=='lv' ) return $interval->format('Pirms stundas');
            if( $locale=='en' ) return $interval->format('%h hour ago');
            if( $locale=='ru' ) return $interval->format('%h час назад');
        }
        if( $interval->h > 1 ){
            if( $locale=='lv' ) return $interval->format('Pirms %h stundām');
            if( $locale=='en' ) return $interval->format('%h hours ago');
            if( $locale=='ru' ) return $interval->format('%h ч. назад');
        }
        if( $interval->i == 1 ){
            if( $locale=='lv' ) return $interval->format('Pirms minūtes');
            if( $locale=='en' ) return $interval->format('%i minute ago');
            if( $locale=='ru' ) return $interval->format('%i минута назад');
        }
        if( $interval->i > 1 ){
            if( $locale=='lv' ) return $interval->format('Pirms %i minūtēm');
            if( $locale=='en' ) return $interval->format('%i minutes ago');
            if( $locale=='ru' ) return $interval->format('%i мин. назад');
        }
        
        if( $locale=='lv' ) return "tikko";
        if( $locale=='en' ) return "now";
        if( $locale=='ru' ) return "только что";
    }

    public function openFile(Request $request){
        return new BinaryFileResponse($this->getParameter('uploads_directory').$request->attributes->get('file'));
     }

    public function index(Request $request){
        
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repository->findBy(['published' => 1]);

        $user_repository = $this->getDoctrine()->getRepository(Admin::class);

        $articletime = array();
        $articleauthor = array();
        for($i = 0; $i<sizeof($articles); $i++){
            if( $articles[$i]->getImage() === null )
                $articles[$i]->setImage("default.png");

            $admin = $user_repository->findOneBy(['username' => $articles[$i]->getAuthor() ]);
            if( $admin !== NULL ){
                $articleauthor[$i] = $admin->getName(); 
            } else {
                $articleauthor[$i] = $articles[$i]->getAuthor();
            }

            $articletime[$i] = $this->dateToTimeElapsed($request, ($articles[$i]->getTime()) );
        }

        $articles = array_reverse($articles);
        $articletime = array_reverse($articletime);
        $articleauthor = array_reverse($articleauthor);


        $event_repository = $this->getDoctrine()->getRepository(Event::class);
        $events = $event_repository->findBy(
            array(),
            array('position' => 'ASC')
        );

        $statuses = array(false, false, false);

        for($i = 0; $i<sizeof($events); $i++){
            $statuses[ $events[$i]->getFinished() ] = true;
        }

        $fullURL = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

        return $this->render('pages/index.html.twig', [
            'controller_name' => 'PagesController',
            'lang' => $request->getLocale(),
            'articles' => $articles,
            'articletime' => $articletime,
            'articleauthor' => $articleauthor,
            'events' => $events,
            'upcoming' => $statuses[0],
            'ongoing' => $statuses[1],
            'finished' => $statuses[2],
            'fullURL' => $fullURL,
            'c' => $this->getContentArray($request)
        ]);//*/
    }

    public function about(Request $request){
        
        return $this->render('pages/about.html.twig', [
            'controller_name' => 'PagesController',
            'lang' => $request->getLocale(),
            'c' =>  $this->getContentArray($request)
        ]);//*/
    }

    public function contact(Request $request){
        
        return $this->render('pages/contact.html.twig', [
            'controller_name' => 'PagesController',
            'lang' => $request->getLocale(),
            'c' =>  $this->getContentArray($request)
        ]);//*/
    }

    public function donate(Request $request){
        
        return $this->render('pages/donate.html.twig', [
            'controller_name' => 'PagesController',
            'lang' => $request->getLocale(),
            'c' =>  $this->getContentArray($request)
        ]);//*/
    }

    public function join(Request $request){
        
        return $this->render('pages/join.html.twig', [
            'controller_name' => 'PagesController',
            'lang' => $request->getLocale(),
            'c' =>  $this->getContentArray($request)
        ]);//*/
    }

    public function privacy(Request $request){
        
        return $this->render('pages/privacy.html.twig', [
            'controller_name' => 'PagesController',
            'lang' => $request->getLocale(),
            'c' =>  $this->getContentArray($request)
        ]);//*/
    }

    public function articles(Request $request){
        
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repository->findBy(['published' => 1]);

        $user_repository = $this->getDoctrine()->getRepository(Admin::class);

        $articletime = array();
        $articleauthor = array();
        for($i = 0; $i<sizeof($articles); $i++){
            if( $articles[$i]->getImage() === null )
                $articles[$i]->setImage("default.png");
            
            
            $admin = $user_repository->findOneBy(['username' => $articles[$i]->getAuthor() ]);
            if( $admin !== NULL ){
                $articleauthor[$i] = $admin->getName(); 
            } else {
                $articleauthor[$i] = $articles[$i]->getAuthor();
            }

            $articletime[$i] = $this->dateToTimeElapsed($request, ($articles[$i]->getTime()) );
        }        

        $articles = array_reverse($articles);
        $articletime = array_reverse($articletime);
        $articleauthor = array_reverse($articleauthor);

        return $this->render('pages/articles.html.twig', [
            'controller_name' => 'PagesController',
            'lang' => $request->getLocale(),
            'articles' => $articles,
            'articletime' => $articletime,
            'articleauthor' => $articleauthor,
            'c' =>  $this->getContentArray($request)
        ]);//*/
    }

    public function author(Request $request){
        
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repository->findBy( ['published' => 1, 'author' => $request->attributes->get('author') ] );

        $user_repository = $this->getDoctrine()->getRepository(Admin::class);

        $articletime = array();
        $articleauthors = array();
        for($i = 0; $i<sizeof($articles); $i++){
            if( $articles[$i]->getImage() === null )
                $articles[$i]->setImage("default.png");
            
            
            $admin = $user_repository->findOneBy(['username' => $articles[$i]->getAuthor() ]);
            if( $admin !== NULL ){
                $articleauthors[$i] = $admin->getName(); 
            } else {
                $articleauthors[$i] = $articles[$i]->getAuthor();
            }

            $articletime[$i] = $this->dateToTimeElapsed($request, ($articles[$i]->getTime()) );
        }        

        $articles = array_reverse($articles);
        $articletime = array_reverse($articletime);
        $articleauthor = $articleauthors[0];

        return $this->render('pages/author.html.twig', [
            'controller_name' => 'PagesController',
            'lang' => $request->getLocale(),
            'articles' => $articles,
            'articletime' => $articletime,
            'articleauthor' => $articleauthor,
            'c' =>  $this->getContentArray($request)
        ]);//*/
    }

    public function tag(Request $request){
        
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $allArticles = $repository->findBy(['published' => 1]);

        $user_repository = $this->getDoctrine()->getRepository(Admin::class);

        $tag = $request->attributes->get("tag");
        for($i = 0; $i<strlen($tag); $i++)
            if( $tag[$i]=='-' ) $tag[$i] = ' ';

        $articletime = array();
        $articleauthor = array();
        $cnt = 0;
        for($i = 0; $i<sizeof($allArticles); $i++){
            $hasTag = false;
            $taglist = json_decode( $allArticles[$i]->getTags() );
            for($j = 0; $j<sizeof($taglist); $j++){
                if( $taglist[$j]==$tag ){
                    $hasTag = true;
                    break;
                }
            }
            if( $hasTag == false ) continue;

            $articles[$cnt] = $allArticles[$i];

            if( $articles[$cnt]->getImage() === null )
                $articles[$cnt]->setImage("default.png");
            
            
            $admin = $user_repository->findOneBy(['username' => $articles[$cnt]->getAuthor() ]);
            if( $admin !== NULL ){
                $articleauthor[$cnt] = $admin->getName(); 
            } else {
                $articleauthor[$cnt] = $articles[$cnt]->getAuthor();
            }

            $articletime[$cnt] = $this->dateToTimeElapsed($request, ($articles[$cnt]->getTime()) );

            $cnt++;
        }        

        $articles = array_reverse($articles);
        $articletime = array_reverse($articletime);
        $articleauthor = array_reverse($articleauthor);

        return $this->render('pages/tag.html.twig', [
            'controller_name' => 'PagesController',
            'lang' => $request->getLocale(),
            'articles' => $articles,
            'articletime' => $articletime,
            'articleauthor' => $articleauthor,
            'tag' => $tag,
            'c' =>  $this->getContentArray($request)
        ]);//*/
    }

    public function article(Request $request){
        
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $article = $repository->findOneBy( array('link' => $request->attributes->get('article_url') ) );

        $user_repository = $this->getDoctrine()->getRepository(Admin::class);

        //$articletime = array();

        $taglist = array();
        $taglist = json_decode( $article->getTags() );
        $taglinks = $taglist;
        for($i = 0; $i<sizeof($taglinks); $i++){
            for($j = 0; $j<strlen($taglinks[$i]); $j++){
                if($taglinks[$i][$j]==' ') $taglinks[$i][$j] = '-';
            }
        }

        if( $article->getImage() === null )
            $article->setImage("default.png");
        
        
        $admin = $user_repository->findOneBy(['username' => $article->getAuthor() ]);
        if( $admin !== NULL ){
            $articleauthor = $admin->getName(); 
        } else {
            $articleauthor = $article->getAuthor();
        }

        //$articletime[$i] = $this->dateToTimeElapsed($request, ($articles[$i]->getTime()) );

        $fullURL = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

        return $this->render('pages/article.html.twig', [
            'controller_name' => 'PagesController',
            'lang' => $request->getLocale(),
            'article' => $article,
            'taglist' => $taglist,
            'taglinks' => $taglinks,
            'articleauthor' => $articleauthor,
            'fullURL' => $fullURL,
            'c' =>  $this->getContentArray($request)
        ]);//*/
    }

    public function documents(Request $request){
        
        $repository = $this->getDoctrine()->getRepository(Document::class);
        $documents = $repository->findBy(
            array(),
            array('position' => 'ASC')
        );

        return $this->render('pages/documents.html.twig', [
            'controller_name' => 'PagesController',
            'lang' => $request->getLocale(),
            'documents' => $documents,
            'c' =>  $this->getContentArray($request)
        ]);//*/
    }

    public function partners(Request $request){
        
        $repository = $this->getDoctrine()->getRepository(Partner::class);
        $partners = $repository->findBy(
            array(),
            array('position' => 'ASC')
        );

        if( count($partners)==0 ) $partners = "NO";

        return $this->render('pages/partners.html.twig', [
            'controller_name' => 'PagesController',
            'lang' => $request->getLocale(),
            'partners' => $partners,
            'c' =>  $this->getContentArray($request)
        ]);//*/
    }

    public function people(Request $request){
        
        $repository = $this->getDoctrine()->getRepository(Person::class);
        $people = $repository->findBy(
            array(),
            array('position' => 'ASC')
        );

        if( count($people)==0 ) $people = "NO";

        return $this->render('pages/people.html.twig', [
            'controller_name' => 'PagesController',
            'lang' => $request->getLocale(),
            'people' => $people,
            'c' =>  $this->getContentArray($request)
        ]);//*/
    }

    public function events(Request $request){
        
        $repository = $this->getDoctrine()->getRepository(Event::class);
        $events = $repository->findBy(
            array(),
            array('position' => 'ASC')
        );

        $statuses = array(false, false, false);

        for($i = 0; $i<sizeof($events); $i++){
            $statuses[ $events[$i]->getFinished() ] = true;
        }

        return $this->render('pages/events.html.twig', [
            'controller_name' => 'PagesController',
            'lang' => $request->getLocale(),
            'events' => $events,
            'upcoming' => $statuses[0],
            'ongoing' => $statuses[1],
            'finished' => $statuses[2],
            'c' =>  $this->getContentArray($request)
        ]);//*/
    }

    public function event(Request $request){
        
        $repository = $this->getDoctrine()->getRepository(Event::class);
        $event = $repository->find( $request->attributes->get('id') );
    
        return $this->render('pages/event.html.twig', [
            'controller_name' => 'PagesController',
            'lang' => $request->getLocale(),
            'event' => $event,
            'c' =>  $this->getContentArray($request)
        ]);//*/
    }

    public function climateMarch(Request $request){

        return $this->render('pages/climateMarch.html.twig', [
            'controller_name' => 'PagesController',
            'lang' => $request->getLocale(),
            'c' =>  $this->getContentArray($request)
        ]);//*/
    }

    /*public function number()
    {
        $number = random_int(0, 100);

        return new Response(
            '<html><body>Lucky number: '.$number.'</body></html>'
        );
    }*/
}