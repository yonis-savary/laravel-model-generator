composer create-project laravel/laravel manual-test
model_generator_path=$(pwd)
cd manual-test
composer config repositories.local-model-generator --json "{\"type\": \"path\", \"url\": \"$model_generator_path\", \"options\": {\"symlink\": false}}"
composer require yonis-savary/laravel-model-generator:dev-main
