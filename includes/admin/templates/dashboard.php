<?php

defined( 'ABSPATH' ) || die();

$next_schedule = wp_next_scheduled('product_updater_calculate_new_prices');
$next_schedule_for_file_generation = wp_next_scheduled('product_updater_generate_orders_sheet');

if ( empty( $next_schedule ) ) {
    return;
}

$remain_time = ( $next_schedule - time() ) / 60;
$file_generation_remaining_time = ( $next_schedule_for_file_generation - time() ) / 60;
?>
<div class="product_updater_main">
    <form action="" method="post" style="display: flex;width: 100%; height: 200px; align-items: center; justify-content: center">
        <div>
            <div style="display: flex; justify-content: center; align-items: center">
                <input type="hidden" name="update_prices" value="1">
                <button class="button button-primary" style="margin-right: 10px;">Update Prices</button>
                <span>It may take time since we are doing it in background as a background process.</span>
            </div>
            <br>
            <span>Anyway, It's going to be updated automatically in : <?php echo intval( $remain_time ); ?> minutes</span>
        </div>
    </form>
    <hr>
    <form action="" method="post" style="display: flex;width: 100%; height: 200px; align-items: center; justify-content: center">
        <div>
            <div style="display: flex; justify-content: center; align-items: center">
                <input type="hidden" name="generate_sheet" value="1">
                <button class="button button-primary" style="margin-right: 10px;">Generate Sheet</button>
                <span>It may take time since we are doing it in background as a background process.</span>
            </div>
            <br>
            <span>Anyway It's going to be generated automatically in : <?php echo intval( $file_generation_remaining_time ); ?> minutes</span>
        </div>
    </form>
    <div style="display: flex;width: 100%; height: 200px; align-items: center; justify-content: center">
        <a href="<?php echo product_updater()->plugin_url() . '/files/sales.xlsx'; ?>" class="button button-primary" style="margin-right: 10px;">Download Sheet</a>
    </div>
</div>
