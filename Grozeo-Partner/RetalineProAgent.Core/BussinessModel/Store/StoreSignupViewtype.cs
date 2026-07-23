
namespace RetalineProAgent.Core.BussinessModel.Store
{
	public enum StoreSignupViewtype
	{
		Default = 0,
		LoginWithPassword = 1,
		LoginWithOTP = 2,
		PendingVerification = 3,
		ForgotPassword = 4,
		VerificationFailedEmail = 5,
		VerificationFailedPhoneNumber = 6,
		GST = 7,
		StoreSignup = 8,
		NoChange = -1,
		InvitationCode = 9,
		SignupWithEmailOTP = 10,
		LoginWithPhone=11,
		ResetPassword=12,
	}
}
