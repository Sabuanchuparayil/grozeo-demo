using RetalineProAgent.Models;

namespace RetalineProAgent.Services;

public interface IUserService
{
    Task<AppUser?> AuthenticateAsync(string email, string password);
    Task<AppUser?> GetByIdAsync(int userId);
    Task<IEnumerable<AppUser>> GetAllAsync(int branchId);
}
