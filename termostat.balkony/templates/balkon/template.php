<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
$this->addExternalJS(SITE_TEMPLATE_PATH.'/assets/js/tour/js/chunk-vendors.js');
$this->addExternalJS(SITE_TEMPLATE_PATH.'/assets/js/tour/js/app.js');
?>

<section class="tour">
</section>

<script>
    window.tour = {
        ajaxUrl: '/local/components/custom/termostat.balkony/ajax.php',
        knockUrl: '<?=SITE_TEMPLATE_PATH?>/assets/music/knock.mp3',
        frameUrl: '<?=SITE_TEMPLATE_PATH?>/3d/',
        baseStep: {
            name: "Включи свое",
           // desc: "Создание \"Тёплых балконов\" является приоритетный направлением нашей компании. Мы предлагаем полный цикл работ по благоустройству балконов и лоджий. От создания дизайн-проекта с учётом всех ваших пожеланий, до полной сдачи объекта \"под ключ\" с гарантией 10 лет по договору.",
            gallery: [
                {
                    img: "<?=SITE_TEMPLATE_PATH?>/assets/img/tour/0.png",
                    name: "пространство"
                }
            ]
        }
    }
</script>
