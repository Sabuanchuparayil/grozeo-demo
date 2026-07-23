using RetalineProAgent.Models;
using System.Security.Cryptography;
using System.Text;

namespace RetalineProAgent.Services;

public class UserService : IUserService
{
    private readonly IDataService _db;

    private const string UserSelectColumns = @"
              u.UserId, u.UserName, u.UserEmail AS Email,
              COALESCE(d.finascop_current_branch_id, 0) AS BranchId,
              CASE WHEN u.IsSuperUser = 'Yes' THEN 'SuperAdmin' ELSE COALESCE(r.RoleName, '') END AS Role,
              CASE WHEN u.IsActive = 'Yes' THEN 1 ELSE 0 END AS IsActive";

    private const string UserFromClause = @"
              FROM finascop_usr_master u
              LEFT JOIN finascop_user_details d ON d.UserId = u.UserId
              LEFT JOIN sys_role r ON r.RoleId = u.RoleId";

    public UserService(IDataService db) => _db = db;

    public async Task<AppUser?> AuthenticateAsync(string email, string password)
    {
        var user = await _db.QueryFirstOrDefaultAsync<AppUser>(
            $@"SELECT {UserSelectColumns}, u.Passwd AS PasswordHash
              {UserFromClause}
              WHERE (u.UserEmail = @Email OR u.UserName = @Email)
                AND u.IsActive = 'Yes' AND u.IsDeleted = 'No'",
            new { Email = email });

        if (user == null || !user.IsActive)
            return null;

        var (verified, needsMigration) = VerifyPassword(password, user.PasswordHash);
        if (!verified)
            return null;

        if (needsMigration)
            await MigratePasswordHashAsync(user.UserId, password);

        user.PasswordHash = string.Empty;
        return user;
    }

    public async Task<AppUser?> GetByIdAsync(int userId) =>
        await _db.QueryFirstOrDefaultAsync<AppUser>(
            $@"SELECT {UserSelectColumns}
              {UserFromClause}
              WHERE u.UserId = @Id AND u.IsActive = 'Yes' AND u.IsDeleted = 'No'",
            new { Id = userId });

    public async Task<IEnumerable<AppUser>> GetAllAsync(int branchId) =>
        await _db.QueryAsync<AppUser>(
            $@"SELECT {UserSelectColumns}
              {UserFromClause}
              INNER JOIN finascop_user_activebranches ab ON ab.UserId = u.UserId AND ab.br_Id = @BranchId
              WHERE u.IsActive = 'Yes' AND u.IsDeleted = 'No'",
            new { BranchId = branchId });

    private async Task MigratePasswordHashAsync(int userId, string plainPassword)
    {
        var bcryptHash = BCrypt.Net.BCrypt.HashPassword(plainPassword);
        await _db.ExecuteAsync(
            "UPDATE finascop_usr_master SET Passwd = @Hash WHERE UserId = @UserId",
            new { Hash = bcryptHash, UserId = userId });
    }

    private static (bool Verified, bool NeedsMigration) VerifyPassword(string plainPassword, string storedHash)
    {
        if (string.IsNullOrEmpty(storedHash))
            return (false, false);

        if (storedHash.StartsWith("$2", StringComparison.Ordinal))
        {
            try
            {
                return (BCrypt.Net.BCrypt.Verify(plainPassword, storedHash), false);
            }
            catch
            {
                return (false, false);
            }
        }

        var md5 = Convert.ToHexString(MD5.HashData(Encoding.UTF8.GetBytes(plainPassword))).ToLowerInvariant();
        if (string.Equals(md5, storedHash, StringComparison.OrdinalIgnoreCase))
            return (true, true);

        return (false, false);
    }
}
