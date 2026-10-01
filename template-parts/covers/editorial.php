<?php
/**
 * Универсальная лёгкая обложка редакционных материалов.
 *
 * @package IoblogEditorial
 */

$cover = wp_parse_args(
	$args ?? array(),
	array(
		'variant' => 'paper',
		'kicker'  => 'IO Blog',
		'title'   => '',
		'caption' => '',
		'edition' => '',
		'alt'     => '',
		'automatic'  => false,
		'motif'      => 'tiles',
		'title_size' => 'regular',
	)
);

// Шаблон принимает только закрытые модификаторы, чтобы метаданные не могли создать произвольный CSS-класс.
$motif      = in_array( $cover['motif'], array( 'tiles', 'orbit', 'rails' ), true ) ? $cover['motif'] : 'tiles';
$title_size = in_array( $cover['title_size'], array( 'regular', 'long', 'xlong' ), true ) ? $cover['title_size'] : 'regular';
$classes    = array( 'io-cover', 'io-cover--editorial', 'io-cover--' . $cover['variant'] );

// Длинный ручной заголовок должен помещаться так же, как заголовок автообложки.
$classes[] = 'io-cover--title-' . $title_size;

// Дополнительные классы нужны только автогенератору; ручные обложки сохраняют прежнюю композицию.
if ( $cover['automatic'] ) {
	$classes[] = 'io-cover--auto';
	$classes[] = 'io-cover--motif-' . $motif;
}
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" role="img" aria-label="<?php echo esc_attr( $cover['alt'] ); ?>">
	<div class="io-cover__canvas" aria-hidden="true">
		<span class="io-cover__brand">IO: BLOG / <?php echo esc_html( $cover['kicker'] ); ?></span>
		<strong class="io-cover__topic"><?php echo esc_html( $cover['title'] ); ?></strong>
		<span class="io-cover__caption"><?php echo esc_html( $cover['caption'] ); ?></span>
		<span class="io-cover__symbol"><i></i><i></i><i></i></span>
		<span class="io-cover__edition"><?php echo esc_html( $cover['edition'] ); ?></span>
	</div>
</div>
