<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Clientes;
use App\Entity\Incidencias;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use \Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\FormBuilder;
class ClienteController extends AbstractController
{
    #[Route('/cliente', name: 'app_cliente')]
    public function index(ManagerRegistry $doctrine): Response{
        if($this->getUser() === null){
            return $this->redirectToRoute("login");
        }
        $repositorio = $doctrine->getRepository(Clientes::class);
        $clientes = $repositorio->findAll();
        return $this->render('cliente/index.html.twig', [
            'controller_name' => 'Clientes',
            'todosLosClientes' => $clientes,
        ]);
    }
    
    /**
     * @Route("/cliente/insertar", name="insertar_cliente")
     */
    public function insertar(Request $request, ManagerRegistry $doctrine): Response {
        if($this->getUser() === null){
            return $this->redirectToRoute("login");
        }
      if ($request->isMethod('POST')) {
            $nombre = $request->request->get('nombre');
            $apellidos = $request->request->get('apellidos');
            $telefono = $request->request->get('telefono');
            $direccion = $request->request->get('direccion');
            $cliente = new Clientes();
            $cliente->setNombre($nombre);
            $cliente->setApellidos($apellidos);
            $cliente->setTelefono($telefono);
            $cliente->setDireccion($direccion);
            $em = $doctrine->getManager();
            $em->persist($cliente);
            $em->flush();
            
            $this->addFlash("aviso", "Cliente Insertado");
            return $this->redirectToRoute("app_cliente");
        } else {
            return $this->render("cliente/insertarCliente.html.twig");
        }
    }
    
        /**
     * @Route("/cliente/insertar2", name="insertar_cliente2")
     */
    public function insertar2(Request $request, ManagerRegistry $doctrine): Response {
        if($this->getUser() === null){
            return $this->redirectToRoute("login");
        }
        $cliente = new Clientes();
        $form = $this->createFormBuilder($cliente)
                ->add('nombre', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor, indique el nombre del cliente.',
                    ]),
                ],
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Ingrese Nombre ',
                    'class' => 'controls'
                )
            ])
                ->add('apellidos',TextType::class, [
                    'label' => false,
                    'attr' => array(
                    'placeholder' => 'Ingrese Apellidos ',
                    'class' => 'controls'
                )
                ] )
                ->add('telefono', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor, indique el teléfono del cliente.',
                    ]),
                ],
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Ingrese Teléfono ',
                    'class' => 'controls'
                )
            ])
                ->add('direccion',TextType::class, [
                    'label' => false,
                    'attr' => array(
                    'placeholder' => 'Ingrese Dirección ',
                    'class' => 'controls'
                )
                ])
                ->add('submit', SubmitType::class, array(
                    'label' => 'Insertar Cliente',
                    'attr'  => array('class' => 'botons')
                ))
                ->getForm();
                ;
        $form->handleRequest($request);
        
        
        if($form->isSubmitted() && $form->isValid()){
            $nombre = $form->get('nombre')->getData();
            $apellidos = $form->get('apellidos')->getData();
            $telefono = $form->get('telefono')->getData();
            $direccion = $form->get('direccion')->getData();
            $cliente->setNombre($nombre);
            $cliente->setApellidos($apellidos);
            $cliente->setTelefono($telefono);
            $cliente->setDireccion($direccion);
            $em = $doctrine->getManager();
            $em->persist($cliente);
            $em->flush();
            
            $this->addFlash("aviso", "Cliente Insertado");
            return $this->redirectToRoute("app_cliente");
        }
        return $this->renderForm('cliente/insertarForm.html.twig', ['form_post'=>$form]);
    }
    
    /**
     *@Route("/cliente/borrar/{id<\d+>}",name="borrar_cliente")
     */
    public function borrar(Clientes $cliente, ManagerRegistry $doctrine): Response{
        if($this->getUser() === null){
            return $this->redirectToRoute("login");
        }
        
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_REMEMBERED')) {
            $this->addFlash("aviso", "Debe de volver a iniciar sesión para eliminar un cliente.");
            return $this->redirectToRoute('app_cliente');
        }
        
        
        $em = $doctrine->getManager();
        $em->remove($cliente);
        $em->flush();
        
        $this->addFlash("aviso", "Cliente borrado");
        return $this->redirectToRoute("app_cliente");
    }
    
    /**
     * @Route("/cliente/{id<\d+>}",name="ver_cliente")
    */
     public function ver(Clientes $clientever, Request $request, ManagerRegistry $doctrine): Response {
        if($this->getUser() === null){
            return $this->redirectToRoute("login");
        }
        $incidencias = new Incidencias(); 
        $repositorio = $doctrine->getRepository(Clientes::class);
        $repositorio2 = $doctrine->getRepository(Incidencias::class);
        $id = $request->get('id');
        $clientever = $repositorio->find($id);
        $incidencias = $repositorio2->findByIdCliente($id);
        
        return $this->render('cliente/verCliente.html.twig', [
            'clienteSeleccionado' => $clientever,
            'incidenciasCliente' => $incidencias,
        ]);
        
        
    }
    
}
