import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\DevAuthController::login
 * @see app/Http/Controllers/Auth/DevAuthController.php:13
 * @route '/api/dev/login'
 */
export const login = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

login.definition = {
    methods: ["post"],
    url: '/api/dev/login',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\DevAuthController::login
 * @see app/Http/Controllers/Auth/DevAuthController.php:13
 * @route '/api/dev/login'
 */
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\DevAuthController::login
 * @see app/Http/Controllers/Auth/DevAuthController.php:13
 * @route '/api/dev/login'
 */
login.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Auth\DevAuthController::login
 * @see app/Http/Controllers/Auth/DevAuthController.php:13
 * @route '/api/dev/login'
 */
    const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: login.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Auth\DevAuthController::login
 * @see app/Http/Controllers/Auth/DevAuthController.php:13
 * @route '/api/dev/login'
 */
        loginForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: login.url(options),
            method: 'post',
        })
    
    login.form = loginForm
const DevAuthController = { login }

export default DevAuthController