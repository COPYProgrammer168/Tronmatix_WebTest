import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::post
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:125
 * @route '/dashboard/password/reset'
 */
export const post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: post.url(options),
    method: 'post',
})

post.definition = {
    methods: ["post"],
    url: '/dashboard/password/reset',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::post
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:125
 * @route '/dashboard/password/reset'
 */
post.url = (options?: RouteQueryOptions) => {
    return post.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::post
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:125
 * @route '/dashboard/password/reset'
 */
post.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: post.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::post
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:125
 * @route '/dashboard/password/reset'
 */
    const postForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: post.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Dashboard\PasswordResetController::post
 * @see app/Http/Controllers/Dashboard/PasswordResetController.php:125
 * @route '/dashboard/password/reset'
 */
        postForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: post.url(options),
            method: 'post',
        })
    
    post.form = postForm
const reset = {
    post: Object.assign(post, post),
}

export default reset