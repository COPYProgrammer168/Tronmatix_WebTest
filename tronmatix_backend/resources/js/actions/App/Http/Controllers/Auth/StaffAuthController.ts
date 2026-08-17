import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\StaffAuthController::login
 * @see app/Http/Controllers/Auth/StaffAuthController.php:16
 * @route '/api/staff/login'
 */
export const login = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

login.definition = {
    methods: ["post"],
    url: '/api/staff/login',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\StaffAuthController::login
 * @see app/Http/Controllers/Auth/StaffAuthController.php:16
 * @route '/api/staff/login'
 */
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\StaffAuthController::login
 * @see app/Http/Controllers/Auth/StaffAuthController.php:16
 * @route '/api/staff/login'
 */
login.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Auth\StaffAuthController::login
 * @see app/Http/Controllers/Auth/StaffAuthController.php:16
 * @route '/api/staff/login'
 */
    const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: login.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Auth\StaffAuthController::login
 * @see app/Http/Controllers/Auth/StaffAuthController.php:16
 * @route '/api/staff/login'
 */
        loginForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: login.url(options),
            method: 'post',
        })
    
    login.form = loginForm
const StaffAuthController = { login }

export default StaffAuthController