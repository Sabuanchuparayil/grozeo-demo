FROM mcr.microsoft.com/dotnet/sdk:6.0 AS build
WORKDIR /src

COPY Retaline.Core/Retaline.Core.csproj Retaline.Core/
COPY Retaline/Retaline.Web.csproj Retaline/
RUN dotnet restore Retaline/Retaline.Web.csproj

COPY Retaline.Core/ Retaline.Core/
COPY Retaline/ Retaline/
RUN dotnet publish Retaline/Retaline.Web.csproj -c Release -o /app/publish --no-restore

FROM mcr.microsoft.com/dotnet/aspnet:6.0 AS runtime
WORKDIR /app
COPY --from=build /app/publish .

ENV ASPNETCORE_ENVIRONMENT=Production
ENV ASPNETCORE_URLS=http://+:${PORT:-8080}

ENTRYPOINT ["dotnet", "Retaline.Web.dll"]
