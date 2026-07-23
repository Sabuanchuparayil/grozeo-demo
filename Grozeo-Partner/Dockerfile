FROM mcr.microsoft.com/dotnet/sdk:8.0 AS build
WORKDIR /src

COPY RetalineProAgent.Net8/RetalineProAgent.csproj RetalineProAgent.Net8/
RUN dotnet restore RetalineProAgent.Net8/RetalineProAgent.csproj

COPY RetalineProAgent.Net8/ RetalineProAgent.Net8/
RUN dotnet publish RetalineProAgent.Net8/RetalineProAgent.csproj -c Release -o /app/publish --no-restore

FROM mcr.microsoft.com/dotnet/aspnet:8.0 AS runtime
WORKDIR /app
COPY --from=build /app/publish .

ENV ASPNETCORE_ENVIRONMENT=Production
ENV ASPNETCORE_URLS=http://+:${PORT:-8082}

ENTRYPOINT ["dotnet", "RetalineProAgent.dll"]
