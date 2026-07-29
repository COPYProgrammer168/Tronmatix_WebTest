import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\GenerateKhqrController::generate
 * @see app/Http/Controllers/Api/GenerateKhqrController.php:80
 * @route '/api/payment/generate-qr'
 */
export const generate = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generate.url(options),
    method: 'post',
})

generate.definition = {
    methods: ["post"],
    url: '/api/payment/generate-qr',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\GenerateKhqrController::generate
 * @see app/Http/Controllers/Api/GenerateKhqrController.php:80
 * @route '/api/payment/generate-qr'
 */
generate.url = (options?: RouteQueryOptions) => {
    return generate.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\GenerateKhqrController::generate
 * @see app/Http/Controllers/Api/GenerateKhqrController.php:80
 * @route '/api/payment/generate-qr'
 */
generate.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generate.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\GenerateKhqrController::generate
 * @see app/Http/Controllers/Api/GenerateKhqrController.php:80
 * @route '/api/payment/generate-qr'
 */
    const generateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: generate.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\GenerateKhqrController::generate
 * @see app/Http/Controllers/Api/GenerateKhqrController.php:80
 * @route '/api/payment/generate-qr'
 */
        generateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: generate.url(options),
            method: 'post',
        })
    
    generate.form = generateForm
const GenerateKhqrController = { generate }

export default GenerateKhqrController