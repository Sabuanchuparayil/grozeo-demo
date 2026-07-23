using QRCoder;
using System;
using System.Collections.Generic;
using System.Drawing.Imaging;
using System.Drawing;
using System.IO;
using System.Linq;
using System.Web;

namespace RetalineProAgent
{
    /// <summary>
    /// Summary description for GenQRCode1
    /// </summary>
    public class GenQRCode1 : IHttpHandler
    {

        public void ProcessRequest(HttpContext context)
        {
            string text = context.Request.QueryString["content"];
            int pixelSize = int.Parse(context.Request.QueryString["pixelSize"] ?? "20");
            string darkColor = context.Request.QueryString["darkColor"] ?? "000000";
            string lightColor = context.Request.QueryString["lightColor"] ?? "FFFFFF";

            if (!string.IsNullOrEmpty(text))
            {
                byte[] qrCodeImage = GenerateQRCodeImage(text, pixelSize, darkColor, lightColor);

                context.Response.ContentType = "image/png";
                context.Response.OutputStream.Write(qrCodeImage, 0, qrCodeImage.Length);
            }
            else
            {
                context.Response.StatusCode = 400;
                context.Response.StatusDescription = "Bad Request: Missing parameter.";
            }
        }

        private byte[] GenerateQRCodeImage(string text, int pixelSize, string darkColor, string lightColor)
        {
            QRCodeGenerator qrGenerator = new QRCodeGenerator();
            QRCodeData qrCodeData = qrGenerator.CreateQrCode(text, QRCodeGenerator.ECCLevel.Q);
            QRCode qrCode = new QRCode(qrCodeData);

            Color darkColorObj = ColorTranslator.FromHtml($"#{darkColor}");
            Color lightColorObj = ColorTranslator.FromHtml($"#{lightColor}");
            Bitmap qrCodeImage = qrCode.GetGraphic(pixelSize, darkColorObj, lightColorObj, true);

            using (MemoryStream ms = new MemoryStream())
            {
                qrCodeImage.Save(ms, ImageFormat.Png);
                return ms.ToArray();
            }
        }

        public bool IsReusable
        {
            get { return false; }
        }
    }
}