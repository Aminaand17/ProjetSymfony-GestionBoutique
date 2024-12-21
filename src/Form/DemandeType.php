<?php 
namespace App\Form;

use App\Entity\Demande;
use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DemandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('articles', CollectionType::class, [
                'entry_type' => ArticleType::class,
                'allow_add' => true,
                'by_reference' => false,
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'En Cours' => 'En Cours',
                    'Acceptée' => 'Acceptée',
                    'Annulée' => 'Annulée',
                ],
            ])
            ->add('quantite', IntegerType::class)
            ->add('submit', SubmitType::class, ['label' => 'Soumettre la demande']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Demande::class,
        ]);
    }
}
