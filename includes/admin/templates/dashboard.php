<?php

defined( 'ABSPATH' ) || die();

$next_schedule = wp_next_scheduled('product_updater_calculate_new_prices');

if ( empty( $next_schedule ) ) {
    return;
}

$remain_time = ( $next_schedule - time() ) / 60;
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
            <span>Anyway, It's going to be updated automatically in : <?php echo esc_html__( number_format( $remain_time , 2) )?> minutes</span>
        </div>
    </form>
    <hr>
    <form action="" method="post" style="display: flex;width: 100%; height: 200px; align-items: center; justify-content: center">
        <div>
            <div style="display: flex; justify-content: center; align-items: center">
                <input type="hidden" name="update_prices" value="1">
                <button class="button button-primary" style="margin-right: 10px;">Update Prices</button>
                <span>It may take time since we are doing it in background as a background process.</span>
            </div>
            <br>
            <span>It's going to be updated automatically in : <?php echo esc_html__( number_format( $remain_time , 2) )?> minutes</span>
        </div>
    </form>
</div>
