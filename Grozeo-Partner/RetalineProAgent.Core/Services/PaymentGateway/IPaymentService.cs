using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.Services.PaymentGateway
{
	public interface IPaymentService
	{
		dynamic PaymentRequest(int merchantId, int packageId, decimal amount, string paymentMehtodId = "");
		int ProcessPayment(dynamic data, int merchantId);
	}
}
