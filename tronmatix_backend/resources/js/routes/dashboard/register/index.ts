import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminAuthController::post
 * @see app/Http/Controllers/AdminAuthController.php:143
 * @route '/dashboard/register'
 */
export const post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: post.url(options),
    method: 'post',
})

post.definition = {
    methods: ["post"],
    url: '/dashboard/register',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AdminAuthController::post
 * @see app/Http/Controllers/AdminAuthController.php:143
 * @route '/dashboard/register'
 */
post.url = (options?: RouteQueryOptions) => {
    return post.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminAuthController::post
 * @see app/Http/Controllers/AdminAuthController.php:143
 * @route '/dashboard/register'
 */
post.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: post.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AdminAuthController::post
 * @see app/Http/Controllers/AdminAuthController.php:143
 * @route '/dashboard/register'
 */
    const postForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: post.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AdminAuthController::post
 * @see app/Http/Controllers/AdminAuthController.php:143
 * @route '/dashboard/register'
 */
        postForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: post.url(options),
            method: 'post',
        })
    
    post.form = postForm
const register = {
    post: Object.assign(post, post),
}

export default register