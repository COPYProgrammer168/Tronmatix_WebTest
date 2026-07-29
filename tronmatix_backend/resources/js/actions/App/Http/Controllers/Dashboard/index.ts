import ActivityLogController from './ActivityLogController'
import StaffController from './StaffController'
import ProductController from './ProductController'
import UserController from './UserController'
import DiscountController from './DiscountController'
import BannerController from './BannerController'
import VideoController from './VideoController'
import ProfileController from './ProfileController'
import SettingsController from './SettingsController'
import DeliveryProviderController from './DeliveryProviderController'
import AdminController from './AdminController'
import TelegramAdminController from './TelegramAdminController'
import FeedbackController from './FeedbackController'
const Dashboard = {
    ActivityLogController: Object.assign(ActivityLogController, ActivityLogController),
StaffController: Object.assign(StaffController, StaffController),
ProductController: Object.assign(ProductController, ProductController),
UserController: Object.assign(UserController, UserController),
DiscountController: Object.assign(DiscountController, DiscountController),
BannerController: Object.assign(BannerController, BannerController),
VideoController: Object.assign(VideoController, VideoController),
ProfileController: Object.assign(ProfileController, ProfileController),
SettingsController: Object.assign(SettingsController, SettingsController),
DeliveryProviderController: Object.assign(DeliveryProviderController, DeliveryProviderController),
AdminController: Object.assign(AdminController, AdminController),
TelegramAdminController: Object.assign(TelegramAdminController, TelegramAdminController),
FeedbackController: Object.assign(FeedbackController, FeedbackController),
}

export default Dashboard