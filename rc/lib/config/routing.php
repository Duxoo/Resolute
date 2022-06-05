<?php
return array(
    '' => 'frontend',
    'api/shops/<shop_id:[^/]+>/screen/<id[^/]+>' => 'frontend/apiScreen',
    'api/shops/<shop_id:[^/]+>/screen' => 'frontend/apiScreens',
    'api/shops/<shop_id:[^/]+>/ingredientCategory/<id[^/]+>' => 'frontend/apiIngredientCategory',
    'api/shops/<shop_id:[^/]+>/ingredientCategory' => 'frontend/apiIngredientCategories',
    'api/shops/<shop_id:[^/]+>/ingredient/<id[^/]+>' => 'frontend/apiIngredient',
    'api/shops/<shop_id:[^/]+>/ingredient' => 'frontend/apiIngredients',
    'api/shops/<shop_id:[^/]+>/addition/<id[^/]+>' => 'frontend/apiAddition',
    'api/shops/<shop_id:[^/]+>/addition' => 'frontend/apiAdditions',
    'api/shops/<shop_id:[^/]+>/offer/<id[^/]+>' => 'frontend/apiOffer',
    'api/shops/<shop_id:[^/]+>/offer' => 'frontend/apiOffers',
    'api/shops/<shop_id:[^/]+>/product/<id[^/]+>' => 'frontend/apiProduct',
    'api/shops/<shop_id:[^/]+>/product' => 'frontend/apiProducts',
    'api/shops/<shop_id:[^/]+>/<url>' => 'frontend/apiError',
    'api/shops/<shop_id:[^/]+>' => 'frontend/api',
);